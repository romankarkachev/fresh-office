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
use common\models\ProductionAttachFilesForm;
use common\models\ProductionFeedbackFiles;

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
                        'actions' => ['index', 'fetch-project-data', 'process-project', 'attach-files', 'temp'],
                        'allow' => true,
                        'roles' => ['root', 'prod_department_head', 'logist'],
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
                // проверим, соответствует ли тип проекта разрешенным и не добавлялся ли этот проект ранее
                if (!in_array($project['type_id'], ProjectsTypes::НАБОР_ДОПУСТИМЫХ_ТИПОВ_ПРОИЗВОДСТВО) ||
                    !in_array($project['state_id'], ProjectsStates::НАБОР_ДОПУСТИМЫХ_СТАТУСОВ_ПРОИЗВОДСТВО) ||
                    ProductionFeedbackFiles::findOne(['project_id' => $project_id])) {
                    return $this->renderPartial('_not_found');
                }

                $project['ca_name'] = trim($project['ca_name']);

                $model = new ProductionFeedbackForm([
                    'project_id' => $project['id'],
                    'ca_id' => $project['ca_id'],
                    'ca_name' => $project['ca_name'],
                    'message_subject' => 'Производство ' . Yii::$app->formatter->asDate(time(), 'php:d F Y') . ' г., ' . $project['ca_name'] . ', проект № ' . $project['id'],
                ]);

                return $this->renderAjax('_project', [
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
            // идентифицируем проект
            $project = foProjects::findOne($model->project_id);

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

            // дополним текст письма параметрами несоответствия - вес и объем по ТТН и фактические
            if ($model->action == 1) {
                $tmpArr = [];
                if (!empty($model->weightTtn)) {
                    $tmpArr[] = ' Вес по ТТН: ' . $model->weightTtn . ' т';
                }

                if (!empty($model->weightFact)) {
                    $tmpArr[] = 'факт. вес: ' . $model->weightFact . ' т';
                }

                if (!empty($model->volumeTtn)) {
                    $tmpArr[] = 'объем по ТТН: ' . $model->volumeTtn . ' м³';
                }

                if (!empty($model->volumeFact)) {
                    $tmpArr[] = 'факт. объем: ' . $model->volumeFact . ' м³';
                }

                $params['body'] .= chr(13) . trim(implode(', ', $tmpArr), ', ');
                unset($tmpArr);
            }

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

            // если груз не соответствует документам, прикладываем Акт взвешивания
            if ($model->action == 1) {
                // папка с шаблонами
                $tmplDir = Yii::getAlias('@uploads-export-templates-fs');
                $wcFfp = $tmplDir . 'weighingCert_' . $model->project_id . '.docx';

                // попробуем идентифицировать транспортное средство
                $transportInfo = '';
                $ferryman = \common\models\Ferrymen::findOne(['name_crm' => $project->ADD_perevoz]);
                if ($ferryman) {
                    $data = str_replace(chr(32), '', mb_strtolower($project->ADD_dannie));
                    $data = str_replace('/', '', $data);
                    $data = str_replace('\\', '', $data);

                    // перевозчик идентифицирован, теперь попробуем найти у него такой транспорт
                    $transportInfo = $ferryman->tryToIdentifyTransport($data);
                }

                $arraySubst = [
                    '%DOC_NUM%' => $model->project_id,
                    '%CA_NAME%' => $model->ca_name,
                    '%TRANSPORT%' => $transportInfo,
                    '%DATE_NUM%' => Yii::$app->formatter->asDate(time(), 'php:d'),
                    '%DATE_MONTH%' => Yii::$app->formatter->asDate(time(), 'php:F'),
                    '%DATE_YEAR%' => Yii::$app->formatter->asDate(time(), 'php:Y г.'),
                ];

                // дополняем Акт табличной частью
                if (!empty($params['mismatches'])) {
                    $iterator = 1;

                    foreach ($params['mismatches'] as $row) {
                        $arraySubst['%TP_NAME_' . $iterator . '%'] = $row['name'];
                        $arraySubst['%TP_VT_' . $iterator . '%'] = $row['value'];
                        $arraySubst['%TP_VF_' . $iterator . '%'] = $row['fact'];

                        $iterator++;
                    }

                    // очистим от переменных оставшиеся строки шаблона, всего их там 10
                    // часть уже заполнена, остальные очищаем
                    for ($i = $iterator; $i <= 10; $i++) {
                        $arraySubst['%TP_NAME_' . $i . '%'] = '';
                        $arraySubst['%TP_VT_' . $i . '%'] = '';
                        $arraySubst['%TP_VF_' . $i . '%'] = '';
                    }
                }

                $docx_gen = new \DocXGen;
                $docx_gen->docxTemplate($tmplDir . 'tmpl_weighingCert.docx', $arraySubst, $wcFfp);

                // сгенерированный Акт добавляем в письмо
                $letter->attach($wcFfp, ['fileName' => 'Акт взвешивания.docx', 'mimeType' => 'application/docx']);
            }

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
                // если почта не отправится (из-за проблем с провайдером, например), то статус закрыть все равно нужно
                try { $email->send(); } catch (\Exception $exception) {}

                unset($email);
            }

            // отправляем письмо также автору проекта, если груз не соответствует документам
            if ($model->action == 1) {
                $email = $letter;
                $receiver = $project->companyManagerCreatorEmailValue;
                if (!empty($receiver)) {
                    $email->setTo($receiver);
                    $email->send();
                }

                unset($receiver);
                unset($email);
            }

            // после отправки письма удаляем файл с Актом взвешивания
            if (!empty($wcFfp) && file_exists($wcFfp)) unlink($wcFfp);

            // выставляем статусы по типам проектов:
            // ЗАКАЗЫ
            // соответствует: "вывоз завершен", потом сразу "одобрено производством"
            // не соответствует: "вывоз завершен", потом сразу "не совпадение"
            //
            // ВЫВОЗЫ
            // соответствует: "вывоз завершен", потом сразу "завершено"
            // не соответствует: "несовпадение", потом сразу "завершено"
            $task_body = 'Производством выявлено несоответствие груза данным CRM. Прошу связаться с клиентом.' . chr(13) . $model->message_body;
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
                        if ($model->action == 1) {
                            // груз документам не соответствует
                            $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_НЕСОВПАДЕНИЕ;
                            if ($project->save()) $success++;

                            $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ЗАВЕРШЕНО;
                            if ($project->save()) $success++;

                            // создаем задачу ответственному
                            ResponsibleForProduction::foapi_createNewTaskForManager($project->ID_COMPANY, $project->ID_MANAGER_VED, $task_body);
                        }
                        elseif ($model->action == 2) {
                            // груз соответствует документам
                            $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ВЫВОЗ_ЗАВЕРШЕН;
                            if ($project->save()) $success++;

                            $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ЗАВЕРШЕНО;
                            if ($project->save()) $success++;
                        }

                        break;
                }

                if ($success == 2)
                    Yii::$app->session->setFlash('success', 'Статус проекта успешно изменен.');
                else
                    Yii::$app->session->setFlash('error', 'Не удалось применить требуемые статусы проекта.');

                // в проекте выставляем вес и объем фактические и по ТТН
                if ($model->action == 1) {
                    // груз документам не соответствует
                    $project->updateAttributes([
                        'ADD_wieght' => $model->weightTtn, // ТТН вес
                        'ADD_vol_ttn' => $model->volumeTtn, // ТТН объем
                        'ADD_weight_true' => $model->weightFact, // Факт вес
                        'ADD_vol_fact' => $model->volumeFact, // Факт объем
                    ]);
                }
                elseif ($model->action == 2) {
                    // груз соответствует документам
                    $project->updateAttributes([
                        'ADD_wieght' => $model->weightTtn, // ТТН вес
                        'ADD_vol_ttn' => $model->volumeTtn, // ТТН объем
                    ]);
                }
            }

            return $this->redirect(['/production']);
        }

        return false;
    }

    /**
     * Рендерит страницу для загрузки файлов логистами либо же выполняет загрузку (если post-запрос).
     * @return mixed
     */
    public function actionAttachFiles()
    {
        $model = new ProductionAttachFilesForm();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                //  идентифицируем проект
                // если он не будет обнаружен, то продолжение не имеет смысла
                if ($model->project === null) {
                    Yii::$app->session->setFlash('error', 'Проект ' . $model->project_id . ' не идентифицирован.');
                }
                else {
                    $files = [];
                    $model->files = UploadedFile::getInstances($model, 'files');
                    if (count($model->files) > 0) {
                        $files = $model->upload();
                        if (false === $files) {
                            Yii::$app->session->setFlash('error', 'Не удалось загрузить файлы.');
                        }
                        else {
                            Yii::$app->session->setFlash('success', 'Файлы успешно добавлены.');
                            return $this->redirect(['/production/attach-files']);
                        }
                    }
                }
            }
        }

        return $this->render('attach_files', ['model' => $model]);
    }

    public function actionTemp()
    {
        $project_id = 32065;
        $project = foProjects::findOne($project_id);
        $model = new ProductionFeedbackForm([
            'action' => 1,
            'project_id' => $project_id,
            'ca_name' => 'Всероссийская академия хороших парней и благородных девиц',
        ]);
        $params = [
            'mismatches' => [
                [
                    'name' => 'Первый отход',
                    'value' => '2000 кг',
                    'fact' => '1870 кг',
                ],
                [
                    'name' => 'Второй отход',
                    'value' => '70 м/рейс',
                    'fact' => '69 м/рейс',
                ],
                [
                    'name' => 'Третий отход',
                    'value' => '500 мин',
                    'fact' => 'отсутствует',
                ],
            ],
        ];

        // папка с шаблонами
        $tmplDir = Yii::getAlias('@uploads-export-templates-fs');
        $ffp = $tmplDir . 'weighingCert_' . $model->project_id . '.docx';

        // попробуем идентифицировать транспортное средство
        $transportInfo = '';
        $ferryman = \common\models\Ferrymen::findOne(['name_crm' => $project->ADD_perevoz]);
        if ($ferryman) {
            $data = str_replace(chr(32), '', mb_strtolower($project->ADD_dannie));
            $data = str_replace('/', '', $data);
            $data = str_replace('\\', '', $data);

            // перевозчик идентифицирован, теперь попробуем найти у него такой транспорт
            $transportInfo = $ferryman->tryToIdentifyTransport($data);
        }

        $arraySubst = [
            '%DOC_NUM%' => $model->project_id,
            '%CA_NAME%' => $model->ca_name,
            '%TRANSPORT%' => $transportInfo,
            '%DATE_NUM%' => Yii::$app->formatter->asDate(time(), 'php:d'),
            '%DATE_MONTH%' => Yii::$app->formatter->asDate(time(), 'php:F'),
            '%DATE_YEAR%' => Yii::$app->formatter->asDate(time(), 'php:Y г.'),
        ];

        // дополняем Акт табличной частью
        $iterator = 1;
        foreach ($params['mismatches'] as $row) {
            $arraySubst['%TP_NAME_' . $iterator . '%'] = $row['name'];
            $arraySubst['%TP_VT_' . $iterator . '%'] = $row['value'];
            $arraySubst['%TP_VF_' . $iterator . '%'] = $row['fact'];

            $iterator++;
        }

        // очистим от переменных оставшиеся строки шаблона, всего их там 10
        // часть уже заполнена, остальные очищаем
        if (count($params['mismatches']) <= 10) {
            for ($i = $iterator; $i <= 10; $i++) {
                $arraySubst['%TP_NAME_' . $i . '%'] = '';
                $arraySubst['%TP_VT_' . $i . '%'] = '';
                $arraySubst['%TP_VF_' . $i . '%'] = '';
            }
        }

        $docx_gen = new \DocXGen;
        $docx_gen->docxTemplate($tmplDir . 'tmpl_weighingCert.docx', $arraySubst, $ffp);

        // сгенерированный Акт добавляем в письмо
        \Yii::$app->response->sendFile($ffp, 'Акт взвешивания.docx', ['mimeType' => 'application/docx']);
        if (file_exists($ffp)) unlink($ffp);
    }
}
