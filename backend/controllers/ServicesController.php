<?php

namespace backend\controllers;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\httpclient\Client;
use kartik\mpdf\Pdf;
use common\models\DirectMSSQLQueries;
use common\models\MailingProjects;
use common\models\ResponsibleByProjectTypes;
use common\models\foProjects;
use common\models\foProjectsSearch;
use common\models\FreshOfficeAPI;
use common\models\ProjectsStates;
use common\models\CorrespondencePackages;
use common\models\PadKinds;
use common\models\PostDeliveryKinds;
use common\models\ProjectsTypes;
use common\models\CounteragentsPostAddresses;
use common\models\Counteragents;
use common\models\TransportRequests;

class ServicesController extends Controller
{
    /**
     * Выполняет отсеивание проектов, оставляя только проекты тех типов, которые переданы в параметрах.
     * @param $projects array массив проектов, который необходимо просеять
     * @param $types array массив идентификаторов типов проектов, которые должны остаться в результате фильтрации
     * @return array
     */
    private function filterProjectsByTypes($projects, $types)
    {
        $result = $projects;

        foreach ($result as $index => $project) {
            if (!in_array($project['type_id'], $types)) {
                unset($result[$index]);
            }
        }

        return $result;
    }

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
        if ($ids != null) {
            $projectsProperties = DirectMSSQLQueries::getProjectsProperties($ids);
            foreach ($projectsProperties as $property) {
                $key = array_search($property['project_id'], array_column($result, 'id'));
                if ($key !== false) {
                    $result[$key]['properties'][] = [
                        'property' => $property['property'],
                        'value' => $property['value'],
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Выполняет дополнение проектов их товарами, хранящимися отдельно.
     * @param $projects array массив проектов из MS SQL
     * @return array дополненный свойствами массив проектов
     */
    private function fillProjectsInvoice($projects)
    {
        $result = $projects;

        $ids = ArrayHelper::getColumn($result, 'id', false);
        $ids = implode(',', $ids);
        if ($ids != null) {
            $projectsProperties = DirectMSSQLQueries::getProjectsInvoices($ids);
            foreach ($projectsProperties as $property) {
                $key = array_search($property['project_id'], array_column($result, 'id'));
                if ($key !== false) {
                    $result[$key]['tp'][] = [
                        'property' => $property['property'],
                        'value' => $property['value'] . ' ' . $property['ED_IZM_TOVAR'],
                    ];
                }
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
            if ($responsible_email == '') continue;
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
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($responsible_email)
                ->setSubject('Подборка новых проектов с типом ' . $projects_to_send[0]['type_name']);

            if ($letter->send()) {
                // запишем в базу данных все идентификаторы отправленных проектов
                foreach ($projects_to_send as $index => $item) {
                    $mp = new MailingProjects();
                    $mp->attributes = $item;
                    $mp->project_id = $item['id'];
                    $mp->type = MailingProjects::MAILING_TYPE_ZAPIER;
                    $mp->save();
                }

                // пауза перед возможной следующей отправкой 2 сек
                sleep(2);
            }
        }
    }

    /**
     * Кодовое название Генриетта (устар. Zapier).
     * Модуль выполняет выборку вновь созданных проектов за период с определенной даты и по заданным типам проектов,
     * и выполняет рассылку тех, которые не были разосланы ранее.
     */
    public function actionMailingByProjectsTypes()
    {
        // делаем выборку получателей по типам проектов
        $receivers = ResponsibleByProjectTypes::find()
            ->select(['type_id' => 'project_type_id', 'receivers'])
            ->where(['in', 'project_type_id', ResponsibleByProjectTypes::PROJECT_TYPES_ZAPIER])
            ->orderBy('project_type_id')
            ->asArray()->all();
        $projects_types = implode(',', ArrayHelper::getColumn($receivers, 'type_id'));
        $receivers = ArrayHelper::map($receivers, 'type_id', 'receivers');

        $time_ago = strtotime(date('Y-m-d', (time() - 7*24*3600)).' 00:00:00'); // отправленные не более семи дней назад

        // отправленные проекты берем не старше одной недели
        $projects_exclude = implode(',', MailingProjects::find()
            ->distinct('project_id')
            ->select('project_id')
            ->where('sent_at > ' . $time_ago)
            ->andWhere(['type' => MailingProjects::MAILING_TYPE_ZAPIER])
            ->column()
        );

        // выборка предположительно новых проектов с определенными типами
        // проекты отбираются не старше одной недели
        $projects = DirectMSSQLQueries::fetchProjectsForMailingByTypes($projects_types, $projects_exclude);

        // сделаем выборку параметров и их значений
        if (count($projects) > 0) {
            // дополним проекты их свойствами, хранящимися в отдельной таблице
            $projects = $this->fillProjectsProperties($projects);

            // перебираем проекты, отсортированные по типу, формируем текст письма из нескольких возможных проектов
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
     * Выборка проектов, отобранных по условиям:
     * Типы: "Заказ предоплата", "Заказ постоплата", "Вывоз", "Самопривоз",
     * Статус: "Транспорт заказан",
     * Дата вывоза: текущая дата +2,
     * Производственная площадка: Ступино.
     * По каждому из таких проектов формируется PDF, который складывается в отдельную папку, потом одно и то же письмо
     * с этими файлами рассылается ответственным менеджерам, заданным в справочнике "Ответственные по типам проектов".
     */
    public function actionMailingPdf()
    {
        // делаем выборку получателей по типам проектов
        $receivers = ResponsibleByProjectTypes::find()
            ->select(['type_id' => 'project_type_id', 'receivers'])
            ->where(['in', 'project_type_id', ResponsibleByProjectTypes::PROJECT_TYPES_PDF])
            ->orderBy('project_type_id')
            ->asArray()->all();
        $receivers = ArrayHelper::map($receivers, 'type_id', 'receivers');

        $time_ago = strtotime(date('Y-m-d', (time() - 7*24*3600)).' 00:00:00'); // отправленные не более семи дней назад

        // отправленные проекты берем не старше одной недели
        $projects_exclude = implode(',', MailingProjects::find()
            ->distinct('project_id')
            ->select('project_id')
            ->where('sent_at > ' . $time_ago)
            ->andWhere(['type' => MailingProjects::MAILING_TYPE_PDF])
            ->column()
        );

        // выборка проектов по условиям, которые указаны в описании к функции
        $projects = DirectMSSQLQueries::fetchProjectsForMailingPDF($projects_exclude);

        if (count($projects) > 0) {
            $for_properties = $this->filterProjectsByTypes($projects, [ProjectsTypes::PROJECT_TYPE_ВЫВОЗ, ProjectsTypes::PROJECT_TYPE_САМОПРИВОЗ]);
            $for_invoice = $this->filterProjectsByTypes($projects, [ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА, ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА]);

            // дополним проекты их свойствами, хранящимися в отдельной таблице
            $for_properties = $this->fillProjectsProperties(array_values($for_properties));
            $for_invoice = $this->fillProjectsInvoice(array_values($for_invoice));
            $projects = array_values(ArrayHelper::merge($for_properties, $for_invoice));

            $filepath = Yii::getAlias('@uploads-temp-pdfs');
            if (!is_dir($filepath)) {
                if (!FileHelper::createDirectory($filepath)) return false;
            }

            // перебираем проекты, отсортированные по типу, формируем текст письма из нескольких возможных проектов
            // и отправляем разом, после этого переходим к следующему типу проектов
            $letter = Yii::$app->mailer->compose([
                'html' => 'projectDataToPDF-html',
            ], [
                'projectType' => $projects[0]['type_name'],
            ])->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameRobert']]);

            $current_type = -1; // текущий тип проектов
            $projects_sent = []; // проекты, которые были успешно отправлены ответственным лицам
            $files_to_delete = []; // файлы, которые будут удалены после отправки письма
            foreach ($projects as $project) {
                if ($project['type_id'] != $current_type) {
                    if ($current_type != -1) {
                        // изменился тип проекта, выполним отправку письма
                        $emails = explode("\n", ArrayHelper::getValue($receivers, $current_type));
                        if (count($projects_sent) > 0) {
                            foreach ($emails as $responsible_email) {
                                $responsible_email = trim($responsible_email);
                                if ($responsible_email == '') continue;
                                // дополним временем отправки и адресатом
                                foreach ($projects_sent as $index => $item) {
                                    $projects_sent[$index]['sent_at'] = time();
                                    $projects_sent[$index]['email_receiver'] = $responsible_email;
                                }

                                $letter->setTo($responsible_email)
                                    ->setSubject('Подборка проектов с производственной площадки (' . $projects_sent[0]['type_name'] . ')');

                                foreach ($files_to_delete as $file) $letter->attach($file);

                                if ($letter->send()) {
                                    // запишем в базу данных все идентификаторы отправленных проектов
                                    foreach ($projects_sent as $index => $item) {
                                        $mp = new MailingProjects();
                                        $mp->attributes = $item;
                                        $mp->project_id = $item['id'];
                                        $mp->type = MailingProjects::MAILING_TYPE_PDF;
                                        if (!$mp->save()) var_dump($mp->errors);
                                    }

                                    // пауза перед возможной следующей отправкой 2 сек
                                    sleep(2);
                                }

                                $letter = Yii::$app->mailer->compose([
                                    'html' => 'projectDataToPDF-html',
                                ], [
                                    'projectType' => $project['type_name'],
                                ])->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameRobert']]);
                            }

                            // удалим все файлы, которые были созданы при подготовке письма
                            foreach ($files_to_delete as $file) unlink($file);
                        }
                        $projects_sent = [];
                        $files_to_delete = [];
                    }
                }

                $filename = $filepath . '/project-' . $project['id'] . '.pdf';
                $pdf = new Pdf([
                    'mode' => Pdf::MODE_UTF8,
                    'destination' => Pdf::DEST_FILE,
                    'filename' => $filename,
                    'cssFile' => 'css/style-pdf.css',
                    'content' => $this->renderPartial('@common/mail/_projectDataForPDF', ['project' => $project]),
                    'options' => ['title' => 'Краткие сведения о проекте ' . $project['id']],
                ]);

                $pdf->render();
                if (file_exists($filename)) {
                    $projects_sent[] = $project;
                    $files_to_delete[] = $filename;
                }

                $current_type = $project['type_id'];
            }

            // и еще раз, потому что если в списке проектов к отправке проекты только одного типа, то условие в цикле
            // не выполнится и отправка не произойдет по этой причине
            $emails = explode("\n", ArrayHelper::getValue($receivers, $current_type));
            if (count($projects_sent) > 0) {
                foreach ($emails as $responsible_email) {
                    $responsible_email = trim($responsible_email);
                    if ($responsible_email == '') continue;
                    // дополним временем отправки и адресатом
                    foreach ($projects_sent as $index => $item) {
                        $projects_sent[$index]['sent_at'] = time();
                        $projects_sent[$index]['email_receiver'] = $responsible_email;
                    }

                    $letter->setTo($responsible_email)
                        ->setSubject('Подборка проектов с производственной площадки (' . $projects_sent[0]['type_name'] . ')');

                    foreach ($files_to_delete as $file) $letter->attach($file);

                    if ($letter->send()) {
                        // запишем в базу данных все идентификаторы отправленных проектов
                        foreach ($projects_sent as $index => $item) {
                            $mp = new MailingProjects();
                            $mp->attributes = $item;
                            $mp->project_id = $item['id'];
                            $mp->type = MailingProjects::MAILING_TYPE_PDF;
                            if (!$mp->save()) var_dump($mp->errors);
                        }

                        // пауза перед возможной следующей отправкой 2 сек
                        sleep(2);
                    }

                    $letter = Yii::$app->mailer->compose([
                        'html' => 'projectDataToPDF-html',
                    ], [
                        'projectType' => $projects_sent[0]['type_name'],
                    ])->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameRobert']]);
                }

                // удалим все файлы, которые были созданы при подготовке письма
                foreach ($files_to_delete as $file) unlink($file);
            }
        }
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
                'fo_id_company' => $project->ca_id,
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
                foProjects::createHistoryRecord($id, ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ, 'Отдано на отправку', 'Формирование документов на отправку');
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
        $query->andWhere(['LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT' => ProjectsStates::STATE_ДОСТАВЛЕНО]);

        $projects = $query->all();

        foreach ($projects as $project) {
            // проверим, есть ли финансы
            if ($project->financesCount > 0) {
                // финансы появились, меняем статус пакета и фиксируем текущую дату и текущее время
                $package = CorrespondencePackages::findOne(['fo_project_id' => $project->ID_LIST_PROJECT_COMPANY]);
                if ($package != null) {
                    $package->paid_at = time();
                    $package->state_id = ProjectsStates::STATE_ЗАВЕРШЕНО;
                    $package->save();
                }

                // меняем статус проекта в CRM
                $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ОПЛАЧЕНО;
                $project->save();

                $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ЗАВЕРШЕНО;
                $project->save();
            }
        }
    }

    /**
     * Выполняет проверку наличия финансов у проектов типов "Заказ предоплата" и "Документы предоплата" в статусе "Счет ожидает оплаты".
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
        $query->andWhere(['LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT' => ProjectsStates::STATE_СЧЕТ_ОЖИДАЕТ_ОПЛАТЫ]);

        $projects = $query->all();

        foreach ($projects as $project) {
            if ($project->financesCount > 0)
                switch ($project->ID_LIST_SPR_PROJECT) {
                    case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА:
                        // меняем статус проекта в CRM
                        $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ОПЛАЧЕНО;
                        $project->save();

                        $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_СОГЛАСОВАНИЕ_ВЫВОЗА;
                        $project->save();

                        break;
                    case ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ:
                        // меняем статус проекта в CRM
                        $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ОПЛАЧЕНО;
                        $project->save();

                        $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ЗАКРЫТИЕ_СЧЕТА;
                        $project->save();

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

            if ($package->track_num != null) {
                // если трек вообще задан, то проверим статус доставки
                $delivered_at = null;
                switch ($package->pd_id) {
                    case PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ:
                        $delivered_at = TrackingController::trackPochtaRu($package->track_num);
                        break;
                    case PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS:
                        $delivered_at = TrackingController::trackMajorExpress($package->track_num);
                        break;
                }

                if ($delivered_at !== false) {
                    // посылка доставлена
                    switch ($package->type_id) {
                        case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА:
                        case ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ:
                            // создание задачи Контроль качества
                            try {
                                // временно отключено по решению заказчика (предварительно до 01.01.2018)
                                //$package->foapi_createNewTaskForManager(FreshOfficeAPI::TASK_TYPE_КОНТРОЛЬ_КАЧЕСТВА, $package->fo_id_company, 92, 'Контроль качества');
                            } catch (\Exception $exception) {

                            }

                            // меняем статус пакета
                            $package->delivered_at = $delivered_at;
                            $package->state_id = ProjectsStates::STATE_ЗАВЕРШЕНО;
                            $package->save();

                            // делаем две записи в истории проекта
                            $package->project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ДОСТАВЛЕНО;
                            $package->project->save();

                            $package->project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ЗАВЕРШЕНО;
                            $package->project->save();

                            break;
                        case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА:
                        case ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ_ПОСТОПЛАТА:
                            $package->delivered_at = $delivered_at;
                            $package->state_id = ProjectsStates::STATE_ДОСТАВЛЕНО;
                            $package->save();

                            // делаем запись в истории проекта
                            $package->project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ДОСТАВЛЕНО;
                            $package->project->save();

                            // создаем задачу
                            // временно отключено по решению заказчика (предварительно до 01.01.2018)
                            //$package->foapi_createNewTaskForManager(FreshOfficeAPI::TASK_TYPE_КОНТРОЛЬ_КАЧЕСТВА, $package->fo_id_company, 92, 'Контроль качества');

                            break;
                    }
                }
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

    /**
     * Делает выборку почтовых адресов контрагентов, создает их в базе веб-приложения при отсутствии.
     */
    public function actionExtractCounteragentsPostAddresses()
    {
        $total_created = 0;

        $array = DirectMSSQLQueries::fetchCounteragentsPostAddresses();
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $item) {
                $exists = CounteragentsPostAddresses::findOne(['src_id' => $item['src_id']]);
                if ($exists == null) {
                    $model = new CounteragentsPostAddresses();
                    $model->attributes = $item;
                    if ($model->save()) {
                        $total_created++;
                    }
                }
            }
        }

        if ($total_created > 0)
            print '<p>Создано новых адресов: ' . $total_created . '.</p>';
        else
            print '<p>Процесс прошел, но новых адресов не создано (вероятно, все оказались в наличии).</p>';
    }

    /**
     * Получает информацию о контрагенте по его ИНН или ОГРН.
     * @param $field_id integer поле для поиска данных (1 - инн, 2 - огрн(ип), 3 - наименование)
     * @param $value string значение для поиска
     * @return array|bool
     */
    public function actionFetchCounteragentsInfoByInnOrgn($field_id, $value)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $type_id = 0;
        // определим тип контрагента по количеству символов кода
        switch ($field_id) {
            case Counteragents::API_FIELD_ИНН:
                if (strlen($value) >= 10) {
                    if (strlen($value) == 10)
                        $type_id = Counteragents::API_CA_TYPE_ЮРЛИЦО;
                    elseif (strlen($value) == 12)
                        $type_id = Counteragents::API_CA_TYPE_ФИЗЛИЦО;
                }
                break;
            case Counteragents::API_FIELD_ОГРН:
                if (strlen($value) >= 13) {
                    if (strlen($value) == 13)
                        $type_id = Counteragents::API_CA_TYPE_ЮРЛИЦО;
                    elseif (strlen($value) == 15)
                        $type_id = Counteragents::API_CA_TYPE_ФИЗЛИЦО;
                }
                break;
        }

        if ($type_id > 0) {
            $details = Counteragents::apiFetchCounteragentsInfo($type_id, $field_id, $value);
            // очищаем от закрытых субъектов предпринимательской деятельности
            $details = Counteragents::api_cleanFromClosed($details);
            if (count($details) == 1) {
                $details = reset($details);
                $model = new Counteragents();
                $result = [];

                switch ($type_id) {
                    case Counteragents::API_CA_TYPE_ЮРЛИЦО:
                        Counteragents::api_fillModelJur($model, $details);
                        $result = [
                            'inn' => $details['inn'],
                            'kpp' => $details['kpp'],
                            'address' => $model->address_j,
                        ];
                        break;
                    case Counteragents::API_CA_TYPE_ФИЗЛИЦО:
                        Counteragents::api_fillModelPhys($model, $details);
                        $result = [
                            'inn' => $details['person']['inn'],
                        ];
                        break;
                }

                $result['ogrn'] = $details['ogrn'];
                $result['name_full'] = $model->name_full;
                $result['email'] = isset($details['email']) ? strtolower($details['email']) : '';
                $result['okved'] = isset($details['mainOkved2']['fullName']) ? $details['mainOkved2']['fullName'] : '';
                if (isset($details['okopf']['code']))
                    if (intval($details['okopf']['code']) == 12300)
                        $result['opf'] = 'ООО';

                if (isset($details['type']))
                    if (intval($details['type']['id']) == 1)
                        $result['opf'] = 'ИП';

                return $result;

            }
        }

        return false;
    }

    /**
     * Получает информацию о банке по его БИК, переданномму в параметрах.
     * Информация поступает из API веб-сервиса.
     * @param $bik string БИК банка
     * @return array|bool
     */
    public function actionFetchBankByBik($bik)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl('http://www.bik-info.ru/api.html')
            ->setData(['bik' => $bik, 'type' => 'json'])
            ->send();

        if ($response->isOk) {
            if (!isset($response->data['error'])) {
                return [
                    'bank_name' => str_replace("&quot;", "&#039;", htmlspecialchars_decode($response->data['name'])) . ' Г. ' . $response->data['city'],
                    'bank_ca' => $response->data['ks'],
                ];
            }
        }

        return false;
    }
}
