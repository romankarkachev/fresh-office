<?php

namespace backend\controllers;

use common\models\DirectMSSQLQueries;
use common\models\foProjects;
use common\models\ProjectsStates;
use common\models\ProjectsTypes;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

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
                    'delete' => ['POST'],
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
            if (count($project) > 0)
                return $this->renderPartial('_project', [
                    'model' => $project,
                ]);
        }

        return $this->renderPartial('_not_found');
    }

    /**
     * В зависимости от ответа пользователя на вопрос "Груз соответствует документам?" производится установка
     * различных статусов.
     */
    public function actionProcessProject()
    {
        $project_id = intval(Yii::$app->request->post('project_id'));
        $action = intval(Yii::$app->request->post('action'));
        if ($project_id > 0 && $action > 0) {
            $project = foProjects::findOne($project_id);
            if ($project != null) {
                switch ($project->ID_LIST_SPR_PROJECT) {
                    case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА:
                    case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА:
                        $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ВЫВОЗ_ЗАВЕРШЕН;
                        $project->save();

                        if ($action == 1) {
                            // груз документам не соответствует
                            $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_НЕСОВПАДЕНИЕ;
                            $project->save();
                        }
                        elseif ($action == 2) {
                            // груз соответствует документам
                            $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ОДОБРЕНО_ПРОИЗВОДСТВОМ;
                            $project->save();
                        }

                        return true;
                    case ProjectsTypes::PROJECT_TYPE_ВЫВОЗ:
                    case ProjectsTypes::PROJECT_TYPE_САМОПРИВОЗ:
                        if ($action == 2) {
                            // груз соответствует документам
                            $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ВЫВОЗ_ЗАВЕРШЕН;
                            $project->save();

                            $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ЗАВЕРШЕНО;
                            $project->save();
                        }

                        return true;
                }
            }
        }

        return false;
    }
}