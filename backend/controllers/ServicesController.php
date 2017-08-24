<?php

namespace backend\controllers;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use common\models\DirectMSSQLQueries;
use common\models\MailingProjects;
use common\models\ResponsibleByProjectTypes;
use common\models\foProjects;
use common\models\foProjectsSearch;
use common\models\foProjectsHistory;
use common\models\FreshOfficeAPI;
use common\models\ProjectsStates;
use common\models\CorrespondencePackages;
use common\models\PadKinds;
use common\models\PostDeliveryKinds;
use common\models\ProjectsTypes;

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
     * Создает запись в истории изменения статусов проекта в CRM.
     * @param $project_id integer идентификатор проекта
     * @param $state_id integer идентификатор нового статуса проекта
     * @param $oldStateName string наименование старого статуса
     * @param $newStateName string наименование нового статуса
     */
    private function createHistoryRecord($project_id, $state_id, $oldStateName, $newStateName)
    {
        $historyModel = new foProjectsHistory();
        $historyModel->ID_LIST_PROJECT_COMPANY = $project_id;
        $historyModel->ID_MANAGER = 73; // freshoffice
        $historyModel->DATE_CHENCH_PRIZNAK = date('Y-m-d\TH:i:s.000');
        $historyModel->TIME_CHENCH_PRIZNAK = Yii::$app->formatter->asDate(time(), 'php:H:i');
        $historyModel->ID_PRIZNAK_PROJECT = $state_id;
        $historyModel->RUN_NAME_CHANCH = 'Изменен статус проeкта c ' . $oldStateName . ' на ' . $newStateName;
        $historyModel->save();
    }

    /**
     * Модуль делает выборку проектов в статусе "Отдано на отправку" из базы Fresh Office и помещает их в базу текущего
     * веб-приложения. Загруженные проекты получают новый статус "Формирование документов на отправку".
     */
    public function actionFetchProjectsToCorrespondence()
    {
        $time_ago = date('Y-m-d', (time() - 7*24*3600)); // семь дней назад

        // загруженные проекты берем не старше одной недели
        $projects_exclude = CorrespondencePackages::find()
            ->distinct('fo_project_id')
            ->select('fo_project_id')
            // берем все проекты пока, если будут проблемы, изменить условие
            //->where('created_at > ' . strtotime($time_ago.' 00:00:00'))
            ->asArray()->column();

        $searchModel = new foProjectsSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'state_id' => ProjectsStates::STATE_ОТДАНО_НА_ОТПРАВКУ,
                // бывает, что проект висит две и три недели, только потом начинаюся движения по документам
                //'searchCreatedFrom' => $time_ago,
                'searchExcludeIds' => $projects_exclude,
            ]
        ]);

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

            if ($model->save())$successIds[] = (string)$project->id;
        }

        if (count($successIds) > 0) {
            foreach ($successIds as $id) {
                // делаем записи в истории изменения статусов проектов
                $this->createHistoryRecord($id, ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ, 'Отдано на отправку', 'Формирование документов на отправку');
            }

            // у успешно затянутых проектов меняем статус в самой CRM
            foProjects::updateAll([
                // статус, который необходимо выставить
                'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT' => ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ,
            ], [
                // идентификаторы проектов, которые подлежат обновлению
                'LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY' => $successIds,
            ]);
        }
    }

    /**
     * Выполняет проверку наличия финансов у проектов типов "Заказ постоплата" и "Документы постоплата" в статусе "Доставлено".
     */
    private function checkFinancesPostPayment()
    {
        $query = foProjects::find();
        $query->select([
            '*',
            'financesCount' =>'FINANCES.COUNT_FINANCE',
            'state_name' => 'LIST_SPR_PRIZNAK_PROJECT.PRIZNAK_PROJECT',
        ]);

        // присоединяем наименования статусов проектов
        $query->leftJoin('LIST_SPR_PRIZNAK_PROJECT', 'LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT = LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT');

        // присоединяем финансы
        $query->leftJoin('(
	        SELECT ID_LIST_PROJECT_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	        FROM LIST_MANYS
	        WHERE
	            ID_SUB_PRIZNAK_MANY = ' . FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ . ' AND
	            ID_NAPR = ' . FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД . '
	        GROUP BY ID_LIST_PROJECT_COMPANY
        ) AS FINANCES', 'FINANCES.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY');

        // условия: типы проектов
        $query->where(['LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT' => [
            ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА,
            ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ_ПОСТОПЛАТА,
        ]]);

        // условия: статусы проектов
        $query->where(['LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT' => ProjectsStates::STATE_ДОСТАВЛЕНО]);

        $projects = $query->all();

        foreach ($projects as $project) {
            if ($project->financesCount > 0) {
                // меняем статус проекта в CRM
                $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ЗАВЕРШЕНО;
                if ($project->save()) {
                    // делаем записи в истории изменения статусов проекта в CRM
                    $this->createHistoryRecord($project->ID_LIST_PROJECT_COMPANY, ProjectsStates::STATE_ОПЛАЧЕНО, $project->state_name, 'Оплачено');

                    $this->createHistoryRecord($project->ID_LIST_PROJECT_COMPANY, ProjectsStates::STATE_ЗАВЕРШЕНО, 'Оплачено', 'Завершено');
                }
            }
        }
    }

    /**
     * Выполняет проверку наличия финансов у проектов типов "Заказ предоплата" и "Документы" в статусе "Счет ожидает оплаты".
     */
    private function checkFinancesAdvancePayment()
    {
        $query = foProjects::find();
        $query->select([
            '*',
            'financesCount' =>'FINANCES.COUNT_FINANCE',
            'state_name' => 'LIST_SPR_PRIZNAK_PROJECT.PRIZNAK_PROJECT',
        ]);

        // присоединяем наименования статусов проектов
        $query->leftJoin('LIST_SPR_PRIZNAK_PROJECT', 'LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT = LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT');

        // присоединяем финансы
        $query->leftJoin('(
	        SELECT ID_LIST_PROJECT_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	        FROM LIST_MANYS
	        WHERE
	            ID_SUB_PRIZNAK_MANY = ' . FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ . ' AND
	            ID_NAPR = ' . FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД . '
	        GROUP BY ID_LIST_PROJECT_COMPANY
        ) AS FINANCES', 'FINANCES.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY');

        // типы проектов
        $query->where(['LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT' => [
            ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА,
            ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ,
        ]]);

        // статусы проектов
        $query->where(['LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT' => ProjectsStates::STATE_СЧЕТ_ОЖИДАЕТ_ОПЛАТЫ]);

        $projects = $query->all();

        foreach ($projects as $project) {
            if ($project->financesCount > 0)
                switch ($project->ID_LIST_SPR_PROJECT) {
                    case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА:
                        // меняем статус проекта в CRM
                        $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_СОГЛАСОВАНИЕ_ВЫВОЗА;
                        if ($project->save()) {
                            // делаем запись в истории изменения статусов проекта в CRM
                            $this->createHistoryRecord($project->ID_LIST_PROJECT_COMPANY, ProjectsStates::STATE_СОГЛАСОВАНИЕ_ВЫВОЗА, $project->state_name, 'Согласование вывоза');
                        }

                        break;
                    case ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ:
                        // меняем статус проекта в CRM
                        $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ЗАКРЫТИЕ_СЧЕТА;
                        if ($project->save()) {
                            // делаем запись в истории изменения статусов проекта в CRM
                            $this->createHistoryRecord($project->ID_LIST_PROJECT_COMPANY, ProjectsStates::STATE_ЗАКРЫТИЕ_СЧЕТА, $project->state_name, 'Закрытие счета');
                        }

                        break;
                }
        }
    }

    /**
     * Делает выборку из веб-приложения проектов в статусе "Отправлено", проверяет треки и отмечает доставленные.
     */
    public function actionCorrespondenceTrackings()
    {
        $packages = CorrespondencePackages::find()
            ->where(['state_id' => ProjectsStates::STATE_ОТПРАВЛЕНО])
            ->andWhere([
                'or',
                ['pd_id' => PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ],
                ['pd_id' => PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS],
            ])
            ->all();

        foreach ($packages as $package) {
            /* @var $package CorrespondencePackages */

            switch ($package->pd_id) {
                case PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ:
                    if ($package->track_num != null) {
                        $delivered_at = TrackingController::trackPochtaRu($package->track_num);
                        if ($delivered_at !== false) {
                            switch ($package->type_id) {
                                case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА:
                                case ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ:
                                    // создание задачи Контроль качества
                                    try {
                                        $package->foapi_createNewTaskForManager(FreshOfficeAPI::TASK_TYPE_КОНТРОЛЬ_КАЧЕСТВА, $package->fo_id_company, 92, 'Контроль качества');
                                    } catch (\Exception $exception) {

                                    }

                                    // меняем статус проекта
                                    $package->delivered_at = $delivered_at;
                                    $package->state_id = ProjectsStates::STATE_ЗАВЕРШЕНО;
                                    $package->save();

                                    // делаем две записи в истории проекта
                                    $this->createHistoryRecord($package->fo_project_id, ProjectsStates::STATE_ДОСТАВЛЕНО, 'Отправлено', 'Доставлено');

                                    $this->createHistoryRecord($package->fo_project_id, ProjectsStates::STATE_ЗАВЕРШЕНО, 'Доставлено', 'Завершено');

                                    break;
                                case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА:
                                case ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ_ПОСТОПЛАТА:
                                    $package->state_id = ProjectsStates::STATE_ДОСТАВЛЕНО;
                                    $package->save();

                                    // делаем запись в истории проекта
                                    $this->createHistoryRecord($package->fo_project_id, ProjectsStates::STATE_ДОСТАВЛЕНО, 'Отправлено', 'Доставлено');

                                    // создаем задачу
                                    $package->foapi_createNewTaskForManager(FreshOfficeAPI::TASK_TYPE_КОНТРОЛЬ_КАЧЕСТВА, $package->fo_id_company, 92, 'Контроль качества');

                                    break;
                            }
                        }
                    }
                    break;
                case PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS:

                    break;
            }
        }

        $this->checkFinancesPostPayment();
    }

    /**
     * ВНИМАНИЕ! Это второй check-finances, в модуле ApiController тоже есть, и это совершенно разные вещи.
     * Делает выборку проектов с типами Заказ предоплата и проверяет появление у них финансов
     * по признаку оплаты Утилизация. Проект переходит в статус Согласование вывоза в случае обнаружения финансов.
     */
    public function actionCheckFinances()
    {
        $this->checkFinancesAdvancePayment();
    }
}
