<?php

namespace backend\controllers;

use common\models\PadKinds;
use common\models\ProjectsTypes;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use common\models\DirectMSSQLQueries;
use common\models\MailingProjects;
use common\models\ResponsibleByProjectTypes;
use common\models\foProjects;
use common\models\foProjectsSearch;
use common\models\ProjectsStates;
use common\models\CorrespondencePackages;

class ServicesController extends Controller
{
    /**
     * Выполняет дополнение проектов их свойствами, хранящимися отдельно.
     * @param $projects array массив проектов из MS SQL
     * @return array дополненный свойствами массив проектов
     */
    private function fillProjectsProperties($projects)
    {
        $result = $projects;

        $ids = ArrayHelper::getColumn($result, 'id', false);
        $ids = implode(',', $ids);
        $projectsProperties = DirectMSSQLQueries::getProjectsProperties($ids);
        foreach ($projectsProperties as $property) {
            $key = array_search($property['project_id'], array_column($projects, 'id'));
            if ($key !== false) {
                $result[$key]['properties'][] = [
                    'property' => $property['property'],
                    'value' => $property['value'],
                ];
            }
        }

        return $result;
    }

    /**
     * Выполняет консолидирование информации, составление текста письма и его отправку.
     * Текст письма состоит из приветствия, списка проектов с их параметрами.
     * @param $emails array массив получателей в разрезе типов проектов
     * @param $projects_to_send array массив проектов к рассылке
     */
    private function sendLetter($emails, $projects_to_send)
    {
        foreach ($emails as $responsible_email) {
            $responsible_email = trim($responsible_email);
            $params = [];
            // дополним временем отправки и адресатом
            foreach ($projects_to_send as $index => $item) {
                $projects_to_send[$index]['sent_at'] = time();
                $projects_to_send[$index]['email_receiver'] = $responsible_email;

                $params['projects'] .= $this->renderPartial('@common/mail/_projectsForMailing', ['project' => $item]);
            }

            $letter = Yii::$app->mailer->compose([
                'html' => 'newProjectsByTypesHasBeenCreated-html',
            ], $params)
                ->setFrom(Yii::$app->params['senderEmail'])
                ->setTo($responsible_email)
                ->setSubject('Подборка новых проектов с типом ' . $projects_to_send[0]['type_name']);

            if ($letter->send()) {
                // запишем в базу данных все идентификаторы отправленных проектов
                foreach ($projects_to_send as $index => $item) {
                    $mp = new MailingProjects();
                    $mp->attributes = $item;
                    $mp->project_id = $item['id'];
                    $mp->save();
                }

                // пауза перед возможной следующей отправкой 2 сек
                sleep(2);
            }
        }
    }

    /**
     * Кодовое название Zapier.
     * Модуль выполняет выборку вновь созданных проектов за период с определенной даты и по заданным типам проектов,
     * и выполняет рассылку тех, которые не были разосланы ранее.
     */
    public function actionMailingByProjectsTypes()
    {
        // делаем выборку получателей по типам проектов
        $receivers = ResponsibleByProjectTypes::find()->select(['type_id' => 'project_type_id', 'receivers'])->orderBy('project_type_id')->asArray()->all();
        $projects_types = implode(',', ArrayHelper::getColumn($receivers, 'type_id'));
        $receivers = ArrayHelper::map($receivers, 'type_id', 'receivers');

        $time_ago = strtotime(date('Y-m-d', (time() - 7*24*3600)).' 00:00:00'); // отправленные не более семи дней назад

        // отправленные проекты берем не старше одной недели
        $projects_exclude = implode(',', MailingProjects::find()->distinct('project_id')->select('project_id')->where('sent_at > ' . $time_ago)->column());

        // выборка предположительно новых проектов с определенными типами
        // проекты отбираются не старше одной недели
        $projects = DirectMSSQLQueries::fetchProjectsForMailingByTypes($projects_types, $projects_exclude);

        // сделаем выборку параметров и их значений
        if (count($projects) > 0) {
            // дополним проекты их свойствами, хранящимися в отдельной таблице
            $projects = $this->fillProjectsProperties($projects);

            // перебираем проекты, отсортированные по типу, формируем текст письма из нескольких возможных проектв
            // и отправляем разом, после этого переходим к следующему типу проектов
            $current_type = -1; // текущий тип проектов
            $projects_sent = []; // проекты, которые были успешно отправлены ответственным лицам
            foreach ($projects as $project) {
                if ($project['type_id'] != $current_type) {
                    if ($current_type != -1) {
                        // изменился тип проекта, выполним отправку письма
                        //$key = array_search(40489, array_column($userdb, 'uid'));
                        $emails = explode("\n", ArrayHelper::getValue($receivers, $current_type));
                        if (count($projects_sent) > 0) $this->sendLetter($emails, $projects_sent);
                        $projects_sent = [];
                    }
                }

                $projects_sent[] = $project;

                $current_type = $project['type_id'];
            }

            // и еще раз, потому что если в списке проектов к отправке проекты только одного типа, то условие в цикле
            // не выполнится и отправка не произойдет по этой причине
            $emails = explode("\n", ArrayHelper::getValue($receivers, $current_type));
            if (count($projects_sent) > 0) $this->sendLetter($emails, $projects_sent);
        }
    }

    /**
     * Модуль делает выборку проектов в статусе "Отдано на отправку" из базы Fresh Office и помещает их в базу текущего
     * веб-приложения. Загруженные проекты получают новый статус "Формирование документов на отправку".
     */
    public function actionFetchProjectsToCorrespondence()
    {
        $time_ago = date('Y-m-d', (time() - 7*24*3600)); // загруженные не более семи дней назад

        // загруженные проекты берем не старше одной недели
        $projects_exclude = CorrespondencePackages::find()->distinct('fo_project_id')->select('fo_project_id')->where('created_at > ' . strtotime($time_ago.' 00:00:00'))->asArray()->column();

        $searchModel = new foProjectsSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'state_id' => ProjectsStates::STATE_ОТДАНО_НА_ОТПРАВКУ,
                'searchCreatedFrom' => $time_ago,
                'searchExcludeIds' => $projects_exclude,
            ]
        ]);

//        echo \yii\grid\GridView::widget([
//            'dataProvider' => $dataProvider,
//            'layout' => '{items}{pager}',
//            'tableOptions' => ['class' => 'table table-striped table-hover'],
//            'columns' => [
//                'id',
//                'created_at',
//                'ca_name',
//                'state_name',
//                'type_id',
//                'type_name',
//            ],
//        ]);

        // типы проектов
        $types = ArrayHelper::map(ProjectsTypes::find()->asArray()->all(), 'id', 'name');

        // виды документов, доступные на данный момент
        $padKinds = PadKinds::find()->select(['id', 'name', 'name_full', 'is_provided' => new Expression(0)])->orderBy('name_full')->asArray()->all();

        $successIds = []; // идентификаторы успешно загруженных в веб-приложение проектов
        foreach ($dataProvider->getModels() as $project) {
            /* @var \common\models\foProjects $project */

            $model = new CorrespondencePackages([
                'fo_project_id' => $project->id,
                'customer_name' => $project->ca_name,
                'state_id' => ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ,
                'pad' => json_encode($padKinds),
            ]);

            if (ArrayHelper::keyExists($project->type_id, $types)) $model->type_id = $project->type_id;

            if ($model->save()) $successIds[] = (string)$project->id;
        }

        if (count($successIds) < -30) {
        //if (count($successIds) > 0) {
            foProjects::updateAll([
                // статус, который необходимо выставить
                'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT' => ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ,
            ], [
                // идентификаторы проектов, которые подлежат обновлению
                'LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY' => $successIds,
            ]);
        }
    }
}