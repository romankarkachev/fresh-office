<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\DirectMSSQLQueries;
use common\models\foProjects;
use common\models\ProductionFeedbackForm;
use common\models\ProjectsStates;
use common\models\ProjectsTypes;
use common\models\ResponsibleForProduction;

/**
 * Контроллер для работы производственного отдела.
 */
class ProductionController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'fetch-project-data', 'process-project'],
                        'allow' => true,
                        'roles' => ['root', 'prod_department_head'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'process-project' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Отображает страницу, с которой можно закрыть производственный проект.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Извлекает данные проекта и возвращает форму.
     * Если проект не будет обнаружен, будет возвращена другая форма с предупреждением.
     * @param $project_id integer идентификатор проекта
     * @return mixed
     */
    public function actionFetchProjectData($project_id)
    {
        $project_id = intval($project_id);
        if ($project_id > 0) {
            $project = DirectMSSQLQueries::fetchProjectsData($project_id);
            if (count($project) > 0) {
                // проверим, соответствует ли тип проекта разрешенным
                if (!in_array($project['type_id'], ProjectsTypes::НАБОР_ДОПУСТИМЫХ_ТИПОВ_ПРОИЗВОДСТВО) ||
                    !in_array($project['state_id'], ProjectsStates::НАБОР_ДОПУСТИМЫХ_СТАТУСОВ_ПРОИЗВОДСТВО)) {
                    return $this->renderPartial('_not_found');
                }

                $project['ca_name'] = trim($project['ca_name']);

                $model = new ProductionFeedbackForm([
                    'project_id' => $project['id'],
                    'ca_id' => $project['ca_id'],
                    'ca_name' => $project['ca_name'],
                    'message_subject' => 'Производство ' . Yii::$app->formatter->asDate(time(), 'php:d F Y') . ' г., ' . $project['ca_name'] . ', проект № ' . $project['id'],
                ]);

                return $this->renderPartial('_project', [
                    'project' => $project,
                    'model' => $model,
                ]);
            }
        }

        return $this->renderPartial('_not_found');
    }

    /**
     * В зависимости от ответа пользователя на вопрос "Груз соответствует документам?" производится установка
     * различных статусов.
     */
    public function actionProcessProject()
    {
        $model = new ProductionFeedbackForm();

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $files = [];
            $model->files = UploadedFile::getInstances($model, 'files');
            if (count($model->files) > 0) {
                $files = $model->upload();
                if (false === $files) {
                    Yii::$app->session->setFlash('error', 'Не удалось загрузить файлы.');
                    return $this->render('_project', [
                        'model' => $model,
                    ]);
                }
            }

            // отправляем загруженные файлы
            $params = [];
            $params['body'] = $model->message_body;
            $params['senderName'] = Yii::$app->user->identity->profile->name;

            if (count($model->tp) > 0) {
                foreach ($model->tp as $row) {
                    if ($row['fact'] != null) $params['mismatches'][] = $row;
                }
            }

            $letter = Yii::$app->mailer->compose([
                'html' => 'productionFeedback-html',
            ], $params)->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameHenrietta']])
                ->setSubject($model->message_subject);

            if (count($files) > 0) foreach ($files as $ffp) $letter->attach($ffp);

            // отправка писем обязательным получателям
            $query = ResponsibleForProduction::find()->where(['type' => ResponsibleForProduction::TYPE_ALWAYS]);
            // набор получателей может дополняться, если выбрано несоответствие
            if ($model->action == 1) {
                $query->orWhere(['type' => ResponsibleForProduction::TYPE_MISMATCH]);
            }
            $receivers = $query->all();

            foreach ($receivers as $receiver) {
                /* @var $receiver ResponsibleForProduction */

                $email = $letter;
                $email->setTo($receiver->receiver);
                $email->send();

                unset($email);
            }

            // выставляем статусы по типам проектов:
            // ЗАКАЗЫ
            // соответствует: "вывоз завершен", потом сразу "одобрено производством"
            // не соответствует: "вывоз завершен", потом сразу "не совпадение"
            //
            // ВЫВОЗЫ
            // соответствует: "вывоз завершен", потом сразу "завершено"
            // не соответствует: -
            $task_body = 'Производством выявлено несоответствие груза данным CRM. Прошу связаться с клиентом.';
            $project = foProjects::findOne($model->project_id);
            if ($project != null) {
                $success = 0;
                switch ($project->ID_LIST_SPR_PROJECT) {
                    case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА:
                    case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА:
                        $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ВЫВОЗ_ЗАВЕРШЕН;
                        if ($project->save()) $success++;

                        if ($model->action == 1) {
                            // груз документам не соответствует
                            $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_НЕСОВПАДЕНИЕ;
                            if ($project->save()) $success++;

                            // создаем задачу ответственному
                            ResponsibleForProduction::foapi_createNewTaskForManager($project->ID_COMPANY, $project->ID_MANAGER_VED, $task_body);
                        }
                        elseif ($model->action == 2) {
                            // груз соответствует документам
                            $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ОДОБРЕНО_ПРОИЗВОДСТВОМ;
                            if ($project->save()) $success++;
                        }

                        break;
                    case ProjectsTypes::PROJECT_TYPE_ВЫВОЗ:
                    case ProjectsTypes::PROJECT_TYPE_САМОПРИВОЗ:
                        // груз соответствует документам
                        $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ВЫВОЗ_ЗАВЕРШЕН;
                        if ($project->save()) $success++;

                        $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ЗАВЕРШЕНО;
                        if ($project->save()) $success++;

                        if ($model->action == 1) {
                            // создаем задачу ответственному
                            ResponsibleForProduction::foapi_createNewTaskForManager($project->ID_COMPANY, $project->ID_MANAGER_VED, $task_body);
                        }

                        break;
                }

                if ($success == 2)
                    Yii::$app->session->setFlash('success', 'Статус проекта успешно изменен.');
                else
                    Yii::$app->session->setFlash('error', 'Не удалось применить требуемые статусы проекта.');
            }

            return $this->redirect(['/production']);
        }

        return false;
    }
}
