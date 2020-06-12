<?php

namespace backend\controllers;

use common\models\EcoMcTp;
use common\models\EcoProjects;
use common\models\EcoProjectsSearch;
use common\models\EcoReportsKinds;
use common\models\PaymentOrders;
use common\models\TransportRequests;
use common\models\TransportRequestsStates;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\httpclient\Client;
use kartik\mpdf\Pdf;
use moonland\phpexcel\Excel;
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
use common\models\City;
use common\models\DadataAPI;
use common\models\Ferrymen;
use common\models\Projects;
use common\models\Regions;
use common\models\YandexServices;
use common\models\foProjectsHistory;
use common\models\foProjectsStates;
use common\models\NotifReceiversStatesNotChangedByTime;
use common\models\NotifReceiversStatesNotChangedTodayForALongTime;
use common\models\ReportPbxAnalytics;
use common\models\pbxCalls;
use common\models\pbxExternalPhoneNumber;
use common\models\CpBlContactEmails;
use common\models\TendersStates;
use common\models\Tenders;
use common\models\TendersLogs;
use common\models\TendersResults;
use common\models\TendersStages;
use common\models\PbxCallsRecognitions;
use common\models\YandexSpeechKitRecognitionQueue;
use common\models\CorrespondencePackagesHistory;
use common\models\CorrespondencePackagesStates;
use common\models\Po;
use common\models\PoAt;
use common\models\PoPop;
use common\models\IncomingMail;
use common\models\PaymentOrdersStates;
use common\models\PoStatesHistory;
use common\models\OutdatedObjectsReceivers;

class ServicesController extends Controller
{
    /**
     * Максимальное количество попыток извлечения проектов для парсинга из них адресов.
     */
    const COLLECT_PROJECTS_TRIES_LIMIT = 5;

    /**
     * Количество проектов, которое необходимо успешно сохранить за проход.
     */
    const COLLECT_PROJECTS_TOTAL_COUNT_PER_CYCLE = 50;

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
        /*
        $projects_exclude = CorrespondencePackages::find()
            ->distinct('fo_project_id')
            ->select('fo_project_id')
            // берем все проекты пока, если будут проблемы, изменить условие
            //->where('created_at > ' . strtotime($time_ago.' 00:00:00'))
            ->asArray()->column();
        */

        $searchModel = new foProjectsSearch();
        $dataProvider = $searchModel->search([
            $searchModel->formName() => [
                'state_id' => ProjectsStates::STATE_ОТДАНО_НА_ОТПРАВКУ,
                // бывает, что проект висит две и три недели, только потом начинаюся движения по документам
                //'searchCreatedFrom' => $time_ago,
                // слишком много проектов в условии уже, переделано на запрос в цикле
                //'searchExcludeIds' => $projects_exclude,
            ]
        ]);

        // типы проектов
        $types = ArrayHelper::map(ProjectsTypes::find()->asArray()->all(), 'id', 'name');

        // виды документов, доступные на данный момент
        $padKinds = PadKinds::find()->select(['id', 'name', 'name_full', 'is_provided' => new Expression(0)])->orderBy('name_full')->asArray()->all();

        $successIds = []; // идентификаторы успешно загруженных в веб-приложение проектов
        foreach ($dataProvider->getModels() as $project) {
            /* @var \common\models\foProjects $project */

            // делаем импорт только в том случае, если проект не был импортирован ранее
            $exist = CorrespondencePackages::findOne(['fo_project_id' => $project->id]);
            if (empty($exist)) {
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

                $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ИСПОЛНЕН;
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
            ProjectsTypes::PROJECT_TYPE_ВЫВОЗ,
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
     * Делает выборку из веб-приложения пакетов в статусе "Отправлено", проверяет треки и отмечает доставленные.
     * correspondence-trackings
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

            if (!empty($package->track_num)) {
                // если трек вообще задан, то проверим статус доставки
                $delivered_at = null;
                switch ($package->pd_id) {
                    case PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ:
                        $delivered_at = TrackingController::trackPochtaRu($package->track_num, false, $package);
                        break;
                    case PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS:
                        $delivered_at = TrackingController::trackMajorExpress($package->track_num);
                        break;
                }

                if (!empty($delivered_at)) {
                    // посылка доставлена
                    if ($package->is_manual) {
                        // меняем статус ручного пакета
                        $package->delivered_at = $delivered_at;
                        $package->state_id = ProjectsStates::STATE_ДОСТАВЛЕНО;
                        $package->save();
                    }
                    else {
                        $project = $package->project;

                        switch ($package->type_id) {
                            case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА:
                            case ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ:
                                // создание задачи Контроль качества
                                /*
                                try {
                                    // временно отключено по решению заказчика (предварительно до 01.01.2018)
                                    $package->foapi_createNewTaskForManager(FreshOfficeAPI::TASK_TYPE_КОНТРОЛЬ_КАЧЕСТВА, $package->fo_id_company, 92, 'Контроль качества');
                                } catch (\Exception $exception) {}
                                */

                                // меняем статус пакета
                                $package->delivered_at = $delivered_at;
                                $package->state_id = ProjectsStates::STATE_ЗАВЕРШЕНО;
                                $package->save();

                                // делаем две записи в истории проекта
                                $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ДОСТАВЛЕНО;
                                $project->save();

                                if ($project->ID_LIST_SPR_PROJECT == ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА) {
                                    $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ИСПОЛНЕН;
                                }
                                else {
                                    $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ЗАВЕРШЕНО;
                                }
                                $project->save();

                                break;
                            case ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА:
                            case ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ_ПОСТОПЛАТА:
                                $package->delivered_at = $delivered_at;
                                $package->state_id = ProjectsStates::STATE_ДОСТАВЛЕНО;
                                $package->save();

                                // делаем запись в истории проекта
                                $project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ДОСТАВЛЕНО;
                                $project->save();

                                // создаем задачу
                                // временно отключено по решению заказчика (предварительно до 01.01.2018)
                                //$package->foapi_createNewTaskForManager(FreshOfficeAPI::TASK_TYPE_КОНТРОЛЬ_КАЧЕСТВА, $package->fo_id_company, 92, 'Контроль качества');

                                break;
                        }
                    }
                }
            }
        }

        $this->checkFinancesPostPayment();
    }

    /**
     * Делает выборку почтовых отправлений в статусе "Отправлено", проверяет треки и отмечает доставленные.
     * in-out-mail-trackings
     * @throws \SoapFault
     * @throws \yii\base\InvalidConfigException
     */
    public function actionInOutMailTrackings()
    {
        foreach (IncomingMail::find()->where([
            'and',
            ['state_id' => ProjectsStates::STATE_ОТПРАВЛЕНО],
            ['is not', 'track_num', null],
        ])->all() as $item) {
            /* @var $item IncomingMail */

            $delivered_at = TrackingController::trackPochtaRu($item->track_num, false);
            if (!empty($delivered_at)) {
                $item->updateAttributes([
                    'state_id' => ProjectsStates::STATE_ДОСТАВЛЕНО,
                ]);
            }
        }
        if (isset($item)) unset($item);

        // также проверим статус отправлений документов по платежным ордерам
        foreach (PaymentOrders::find()->where([
            'and',
            ['is not', 'imt_num', null],
            ['imt_state' => PaymentOrders::INCOMING_MAIL_STATE_В_ПУТИ],
        ])->all() as $item) {
            /* @var $item PaymentOrders */

            $delivered_at = TrackingController::trackPochtaRu($item->imt_num, false, $item);
            if (!empty($delivered_at)) {
                $item->updateAttributes([
                    'imt_state' => PaymentOrders::INCOMING_MAIL_STATE_ПОЛУЧЕНО,
                ]);
            }
        }
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
     * Получает информацию о контрагенте по его ИНН или ОГРН через сервис ОГРН.ОНЛАЙН.
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
     * Получает подробную информацию о контрагенте через сервис dadata.ru.
     * fetch-counteragents-info-dadata
     * @param $query string ИНН или ОГРН контрагента
     * @param $specifyingValue string КПП для уточнения
     * @param $cleanDir integer
     * @return array|false
     */
    public function actionFetchCounteragentsInfoDadata($query, $specifyingValue = null, $cleanDir = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return DadataAPI::fetchCounteragentsInfo($query, $specifyingValue, $cleanDir);
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

    /**
     * Сбор данных для модуля "Подбор перевозчиков".
     * Выполняется выборка проектов с id больше чем уже есть в текущей базе. Всего берется столько проектов, сколько
     * указано в константе, но не более 10 попыток. В ходе каждой итерации идет попытка наполнить пакет до максимального
     * количества, указанного в константе. Как только это удается, сразу идет запись пакета в базу. Если по истечении
     * максимального количества попыток не удалось набрать достаточное количество проектов, то сохраняется их столько,
     * сколько есть.
     * Для каждого проекта выполняется попытка определить перевозчика, регион и город. Успешные отмечаются признаком.
     * collect-projects
     */
    public function actionCollectProjects()
    {
        // вся работа идет в предеах цикла с четким лимитом попыток
        $heap = []; // массив значений для будущих (новых) проектов
        $tryIterator = 0;

        // вспомогательные данные
        // перевозчики из нашей базы
        $ferrymen = Ferrymen::arrayMapForSearchByCrmName();

        // регионы
        $regions = Regions::arrayMapOnlyRussiaForSearchByName();

        // населенные пункты
        $cities = City::find()->where(['country_id' => Regions::COUNTRY_RUSSIA_ID])->asArray()->all();

        while ($tryIterator < self::COLLECT_PROJECTS_TRIES_LIMIT) {
            print '<p>Попытка извлечения проектов № ' . ($tryIterator+1) . '.<p>';

            $projectsStored = 0;
            // берем только последний проект, нам нужен его идентификатор для продолжения сбора информации
            // если идентификатора не окажется, то не страшно, значит просто возьмем с первого
            $lastProject = Projects::find()->orderBy('id DESC')->one();
            $query = foProjects::find()->select([
                'LIST_PROJECT_COMPANY.*',
                'id' => 'LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY',
                'adres' => 'VALUES_PROPERTIES_PROGECT',
                'payment.amount',
                'payment.cost',
            ])->where([
                // статусы любые, исключая Неверное оформление и Отказ клиента
                'not in', 'LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT', [ProjectsStates::STATE_НЕВЕРНОЕ_ОФОРМЛЕНИЕ_ЗАЯВКИ, ProjectsStates::STATE_ОТКАЗ_КЛИЕНТА]
            ])->andWhere([
                // типы только Заказы и Вывоз
                'LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT' => [ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА, ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА, ProjectsTypes::PROJECT_TYPE_ВЫВОЗ],
            ])->andWhere([
                'or',
                ['LIST_PROJECT_COMPANY.TRASH' => null],
                ['LIST_PROJECT_COMPANY.TRASH' => 0],
            ])->leftJoin(
                'LIST_PROPERTIES_PROGECT_COMPANY',
                'LIST_PROPERTIES_PROGECT_COMPANY.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY AND LIST_PROPERTIES_PROGECT_COMPANY.ID_LIST_PROPERTIES_PROGECT IN (12,20,34)'
            )->leftJoin('(
                SELECT ID_LIST_PROJECT_COMPANY, SUM(PRICE_TOVAR * KOLVO) AS amount, SUM(SS_PRICE_TOVAR * KOLVO) AS cost
	            FROM [CBaseCRM_Fresh_7x].[dbo].[LIST_TOVAR_PROJECT]
	            GROUP BY ID_LIST_PROJECT_COMPANY
            ) AS payment', 'payment.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY'
            )->orderBy('LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY')->limit(self::COLLECT_PROJECTS_TOTAL_COUNT_PER_CYCLE);

            if (!empty($lastProject)) $query->andWhere('LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY > ' . $lastProject->id);
            $projects = $query->all();
            foreach ($projects as $project) {
                /* @var $project foProjects */
                print '<p>Обработка проекта ' . $project->id . '.<p>';

                // проверим, не был ли текущий проект добавлен ранее
                $alreadyProject = Projects::findOne($project->id);
                if (empty($alreadyProject)) {
                    // проект отсутствует в нашей базе, добавим его в массив
                    // но предварительно подготовим данные для полей
                    print '<p>Проект обрабатывается.<p>';

                    // адрес
                    // если в проекте не окажется адрес, мы его вообще брать не будем
                    $address = null;
                    if (!empty($project->ADD_adres)) $address = $project->ADD_adres; else $address = $project->adres;
                    if ($address === '') $address = null;

                    // регион и населенный пункт
                    $region_id = null;
                    $city_id = null;
                    if (!empty($address)) {
                        try {
                            $data = YandexServices::getRequestToApi($address);
                            sleep(1);
                            if ($data !== false) {
                                if (!empty($data['Components'])) {
                                    $key = array_search('province', array_column($data['Components'], 'kind'));
                                    if ($key !== false) {
                                        if (false !== mb_stripos($data['Components'][$key]['name'], 'округ')) {
                                            unset($data['Components'][$key]);
                                            unset($key);
                                            $data['Components'] = array_values($data['Components']);
                                            $key = array_search('province', array_column($data['Components'], 'kind'));
                                            if ($key !== false) $region = $data['Components'][$key]['name'];
                                        } else $region = $data['Components'][$key]['name'];
                                    } else print '<p>Округ не найден.</p>';

                                    if (isset($region)) {
                                        $region = str_replace('область', '', $region);
                                        $region = str_replace('республика', '', $region);
                                        $region = trim($region);
                                        $region_id = array_filter($regions, function ($id, $name) use ($region) {
                                            if (mb_stripos($name, $region) !== false) return true;
                                            return false;
                                        }, ARRAY_FILTER_USE_BOTH);
                                        // берем значение первого элемента возвращенного массива, если оно есть
                                        if (count($region_id) > 0) {
                                            $region_id = current($region_id);
                                            $key = array_search('locality', array_column($data['Components'], 'kind'));
                                            if ($key !== false) {
                                                $city = $data['Components'][$key]['name'];
                                                $regionSelected = array_filter($cities, function ($element) use ($region_id, $city) {
                                                    //echo '<p>Поиск города ' . $city . ' по региону ' . $region_id . ': ' . $element . $val . '</p>';
                                                    if ($element['region_id'] == $region_id && mb_stripos($element['name'], $city) !== false) return true;
                                                    return false;
                                                });
                                                if (count($regionSelected) > 0) $city_id = current($regionSelected)['city_id'];
                                            }
                                        }
                                        else $region_id = null;
                                    }
                                }
                            }
                        } catch (\Exception $exception) {var_dump($exception);}
                    }

                    // перевозчик
                    $ferryman = null;
                    if (!empty($project->ADD_perevoz)) {
                        try {
                            $ferryman_id = ArrayHelper::getValue($ferrymen, $project->ADD_perevoz);
                            if (!empty($ferryman_id)) $ferryman = $ferryman_id;
                            unset($ferryman_id);
                        }
                        catch (\Exception $exception) {}
                    }

                    // добавляем в массив новых проектов, но только если его еще там нет
                    if (count(array_filter($heap, function($innerArray) use ($project) {
                        return ($innerArray[0] == $project->id);
                    })) == 0) {
                        $heap[] = [
                            // id
                            $project->id,
                            // created_at
                            strtotime($project->DATE_CREATE_PROGECT),
                            // address берется из проекта, а если нет, то из параметров, а если и там нет, то все
                            $address,
                            // data
                            $project->ADD_dannie,
                            // ferryman_origin
                            (!empty($project->ADD_perevoz) ? $project->ADD_perevoz : null),
                            // comment
                            $project->PRIM_PROJECT_COMPANY,
                            // region_id
                            $region_id,
                            // city_id
                            $city_id,
                            // ferryman_id
                            $ferryman,
                            // себестоимость
                            $project->cost,
                            // стоимость
                            $project->amount,
                        ];
                        $projectsStored++;
                        //print '<p>Проект ' . $project->id . ' помещен в пакет.</p>';
                    }
                    //var_dump($heap);
                    //print '<p>Проектов собрано: ' . $projectsStored . '.</p>';
                    // если достаточное количество проектов уже собрано, выходим из обоих циклов, чтобы выполнить
                    // сохранение этих проектов пачкой
                    if ($projectsStored == self::COLLECT_PROJECTS_TOTAL_COUNT_PER_CYCLE) { $tryIterator++; break 2; }
                }
                else print '<p>Проект существует, и будет пропущен.<p>';
            }
            $tryIterator++;
        }

        print '<p>Завершено на попытке ' . $tryIterator . '.<p>';
        if (count($heap) > 0) {
            print '<p>Необходимое для записи количество проектов собрано, выполняется попытка сохранения. В пачке проектов: ' . count($heap) . '.<p>';
            $rowsAffected = Yii::$app->db->createCommand()->batchInsert('projects', [
                'id', 'created_at', 'address', 'data', 'ferryman_origin', 'comment', 'region_id', 'city_id', 'ferryman_id', 'cost', 'amount'
            ], $heap)->execute();
            print '<p>Запрос по сохранению пачки выполнен, строк затронуто: ' . $rowsAffected . '.<p>';
        }
    }

    /**
     * Делает выборку проектов, последнее изменение статуса которых было более заданного количества часов назад.
     * Проблемные проекты отправляются на почту.
     * notify-about-outdated-projects-today
     */
    public function actionNotifyAboutOutdatedProjectsToday()
    {
        $currentDay = time();
        $timeAgo = time() - 4 * 3600; // за последние четыре часа
        $stateChangedTimeAgo = new \yii\db\Expression('CONVERT(datetime, \'' . date('Y-m-d', $timeAgo) . 'T' . date('H:i:s', $timeAgo) . '.000\', 126)');
        $currentDayMsSqlFormat = new \yii\db\Expression('CONVERT(datetime, \'' . date('Y-m-d', $currentDay) . 'T00:00:00.000\', 126)');

        $query = foProjects::find()
            ->select([
                'id' => 'ID_LIST_PROJECT_COMPANY',
            ])
            ->where([
                'ID_LIST_SPR_PROJECT' => ProjectsTypes::НАБОР_ВЫВОЗ_ЗАКАЗЫ,
                'ADD_vivozdate' => $currentDayMsSqlFormat,
            ])
            ->andWhere('[ID_PRIZNAK_PROJECT] <> ' . ProjectsStates::STATE_ЗАВЕРШЕНО)
            ->andWhere('(
	SELECT TOP 1 [DATE_CHENCH_PRIZNAK] FROM CBaseCRM_Fresh_7x.dbo.LIST_HISTORY_PROJECT_COMPANY
	WHERE [LIST_HISTORY_PROJECT_COMPANY].[ID_LIST_PROJECT_COMPANY] = [LIST_PROJECT_COMPANY].[ID_LIST_PROJECT_COMPANY]
	ORDER BY [DATE_CHENCH_PRIZNAK] DESC
) <= ' . $stateChangedTimeAgo);
        // выполним запрос и сразу переведем проекты в строку через запятую
        $projectsIds = implode(', ', $query->asArray()->column());

        if (!empty($projectsIds)) {
            // выборка готова, готовим письмо
            $letter = Yii::$app->mailer->compose([
                'html' => 'projectsStatesNotChangedToday-html',
            ], [
                'date' => Yii::$app->formatter->asDate($currentDay, 'php:d F Y'),
                'projectsIds' => $projectsIds,
            ])
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNashville']])
                ->setSubject('Обновите статусы в CRM');

            // рассылаем письмо получателям уведомлений
            foreach (NotifReceiversStatesNotChangedTodayForALongTime::find()->select('receiver')->asArray()->column() as $receiver) {
                $letter->setTo($receiver);
                $letter->send();
            }

            // отправка боссу отдельным письмом индивидуально
            $letter->setTo('bugrovap@gmail.com');
            $letter->send();
        }
    }

    /**
     * Выполняет отправку письма с просроченными проектами (пакетами).
     * Пример: array(2) { [0]=> array(3) { ["stateName"]=> string(33) "Дежурный менеджер" ["time"]=> string(6) "604800" ["projects"]=> array(2) { [0]=> int(19776) [1]=> int(18746) } } [1]=> array(3) { ["stateName"]=> string(17) "На складе" ["time"]=> string(5) "28800" ["projects"]=> array(3) { [0]=> int(20726) [1]=> int(20743) [2]=> int(20571) } } } array(1) { [0]=> array(3) { ["stateName"]=> string(27) "Вывоз завершен" ["time"]=> string(6) "172800" ["projects"]=> array(5) { [0]=> int(19653) [1]=> int(19503) [2]=> int(20373) [3]=> int(18350) [4]=> int(20629) } } }
     * @param $template string наименование шаблона письма
     * @param $subject string тема письма
     * @param $receiver string E-mail получателя уведомления
     * @param $data array массив с просроченными проектами по статусам
     */
    public function sendEmailNotificationAboutOudatedByCustomTime($receiver, $data, $template = 'projectsStatesNotChangedByCustomTime', $subject = 'Обновите статусы в CRM')
    {
        Yii::$app->mailer->compose([
            'html' => $template . '-html',
        ], [
            'items' => $data,
        ])
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNashville']])
            ->setSubject($subject)
            ->setTo($receiver)
            ->send();
        ;
    }

    /**
     * Делает выборку незавершенных проектов, просматривает их статусы и сравнивает допустимые сроки нахождения в этих статусах.
     * Если срок пребывания проекта в некотором статусе превышен, то проект добавляется в письмо.
     * notify-about-projects-outdated-by-custom-time
     */
    public function actionNotifyAboutProjectsOutdatedByCustomTime()
    {
        // получатели уведомлений по статусам и допустимым периодам
        $receivers = NotifReceiversStatesNotChangedByTime::find()->where(['section' => NotifReceiversStatesNotChangedByTime::SECTION_ПРОЕКТЫ])->orderBy('receiver')->asArray()->all();

        $query = foProjects::find()
            ->select([
                'id' => '[LIST_PROJECT_COMPANY].[ID_LIST_PROJECT_COMPANY]',
                'state_id' => '[currentStates].[ID_PRIZNAK_PROJECT]',
                'changed_at' => '[currentStates].[DATE_CHENCH_PRIZNAK]',
            ])
            ->where([
                '[LIST_PROJECT_COMPANY].[ID_LIST_SPR_PROJECT]' => ProjectsTypes::НАБОР_ВЫВОЗ_ЗАКАЗЫ_ДОКУМЕНТЫ,
            ])
            ->andWhere([
                'not in',
                '[LIST_PROJECT_COMPANY].[ID_PRIZNAK_PROJECT]',
                ProjectsStates::НАБОР_ИСКЛЮЧЕНИЙ_ДЛЯ_ОПОВЕЩЕНИЯ_О_ПРОСРОЧЕННЫХ_ПРОЕКТАХ,
            ])
            ->andWhere('[currentStates].[ID_PRIZNAK_PROJECT] <> [LIST_PROJECT_COMPANY].[ID_LIST_PROJECT_COMPANY]')
            ->join('OUTER APPLY', '(
                SELECT TOP 1 [ID_LIST_PROJECT_COMPANY], [ID_PRIZNAK_PROJECT], [DATE_CHENCH_PRIZNAK]
                FROM ' . foProjectsHistory::tableName() . '
                WHERE ' . foProjectsHistory::tableName() . '.[ID_LIST_PROJECT_COMPANY] = [LIST_PROJECT_COMPANY].[ID_LIST_PROJECT_COMPANY]
                ORDER BY [DATE_CHENCH_PRIZNAK] DESC
            ) AS currentStates');

        foreach ($query->asArray()->all() as $project) {
            $key = array_search($project['state_id'], array_column($receivers, 'state_id'));
            if (false !== $key) {
                // опишем статус именем, запрос делается только один раз
                if (empty($receivers[$key]['stateName'])) {
                    // если статус все еще обезличен, то узнаем его наименование
                    $state = foProjectsStates::findOne($project['state_id']);
                    if ($state) {
                        $receivers[$key]['stateName'] = $state->PRIZNAK_PROJECT;
                    }
                    unset($state);
                }

                // есть такой статус на контроле, проверим, не просрочен ли он
                $changedAt = strtotime($project['changed_at']); // время приобретения статуса проектом как число
                $outdatedTermin = time() - $receivers[$key]['time'];
                if ($changedAt < $outdatedTermin) {
                    // статус просрочен, поместим проект в списк проблемных
                    $receivers[$key]['projects'][] = $project['id'];
                }
            }
            //else print '<p>Статус ' . $project['state_id'] . ' для проекта ' . $project['id'] . ' не найден.</p>';
        }

        $bossArray = []; // сюда собираются различные статусы для отправки боссу одним письмом
        $prevArray = [];
        $currentReceiver = -1;
        foreach ($receivers as $receiver) {
            // если по статусу проектов нет, переходим к следующему комплекту
            if (empty($receiver['projects'])) continue;

            if ($currentReceiver != $receiver['receiver'] && $currentReceiver != -1) {
                // выполняем отправку сообщения
                $this->sendEmailNotificationAboutOudatedByCustomTime($currentReceiver, $prevArray);
                $prevArray = [];
            }

            // найдем статус в подготовленном к отправке массиве
            // если такой статус туда уже помещен, то дополним его проектами
            // а если нет, то создадим новый
            $key = array_search($receiver['stateName'], array_column($prevArray, 'stateName'));
            if (false !== $key) {
                ArrayHelper::merge($prevArray[$key], $receiver['projects']);
                ArrayHelper::merge($bossArray[$key], $receiver['projects']);
            }
            else {
                $record = [
                    'stateName' => $receiver['stateName'],
                    'time' => $receiver['time'],
                    'projects' => $receiver['projects'],
                ];
                $prevArray[] = $record;
                $bossArray[] = $record;
            }

            $currentReceiver = $receiver['receiver'];
        }

        // еще раз отправку для последней сборки проектов
        if (count($prevArray) > 0) $this->sendEmailNotificationAboutOudatedByCustomTime($currentReceiver, $prevArray);
        // отправим боссу одним письмом все различные статусы
        if (count($bossArray) > 0) $this->sendEmailNotificationAboutOudatedByCustomTime('bugrovap@gmail.com', $bossArray);
    }

    /**
     * Делает выборку пакетов, просматривает их статусы и сравнивает допустимые сроки нахождения в этих статусах.
     * Если срок пребывания пакета в некотором статусе превышен, то пакет добавляется в письмо.
     * notify-about-cp-outdated-by-custom-time
     */
    public function actionNotifyAboutCpOutdatedByCustomTime()
    {
        $template = 'сpStatesNotChangedByCustomTime';
        $subject = 'Пакеты корреспоенденции без движения';

        // получатели уведомлений по статусам и допустимым периодам
        $receivers = NotifReceiversStatesNotChangedByTime::find()->where(['section' => NotifReceiversStatesNotChangedByTime::SECTION_ПАКЕТЫ])->orderBy('receiver')->asArray()->all();

        // статусы пакетов
        $statesCp = CorrespondencePackagesStates::arrayMapForSelect2();

        $query = CorrespondencePackages::find()
            ->select([
                'id' => CorrespondencePackages::tableName() . '.`id`',
                'customer_name' => CorrespondencePackages::tableName() . '.`customer_name`',
                'cps_id' => CorrespondencePackages::tableName() . '.`cps_id`',
                'obtainedAt' => CorrespondencePackagesHistory::find()->select('created_at')->where([CorrespondencePackagesHistory::tableName() . '.`cp_id`' => new Expression(CorrespondencePackages::tableName() . '.`id`')])->limit(1)->orderBy('created_at DESC'),
            ])
            ->where(['cps_id' => NotifReceiversStatesNotChangedByTime::НАБОР_СТАТУСОВ_ДЛЯ_ОПОВЕЩЕНИЯ_О_ПРОСРОЧЕННЫХ_ПАКЕТАХ]);

        foreach ($query->asArray()->all() as $package) {
            foreach ($receivers as $index => $receiver) {
                if ($receiver['state_id'] != $package['cps_id']) continue;

                // опишем статус именем, запрос делается только один раз
                if (empty($receivers[$index]['stateName'])) {
                    // если статус все еще обезличен, то узнаем его наименование
                    $receivers[$index]['stateName'] = ArrayHelper::getValue($statesCp, $package['cps_id']);
                }

                // есть такой статус на контроле, проверим, не просрочен ли он
                $changedAt = strtotime($package['obtainedAt']); // время приобретения статуса пакетом как число
                $outdatedTermin = time() - $receivers[$index]['time'];
                if ($changedAt < $outdatedTermin) {
                    // статус просрочен, поместим пакет в списк проблемных
                    $receivers[$index]['packages'][] = \yii\helpers\Html::a($package['id'] . (!empty($package['customer_name']) ? ' (' . trim($package['customer_name']) . ')' : ''), Yii::$app->urlManager->createAbsoluteUrl(['/correspondence-packages/update', 'id' => $package['id']]), ['class' => 'btn-link']);
                }
            }
        }

        $prevArray = [];
        $currentReceiver = -1;
        foreach ($receivers as $receiver) {
            // если по статусу пакетов нет, переходим к следующему комплекту
            if (empty($receiver['packages'])) continue;

            if ($currentReceiver != $receiver['receiver'] && $currentReceiver != -1) {
                // выполняем отправку сообщения
                $this->sendEmailNotificationAboutOudatedByCustomTime($currentReceiver, $prevArray, $template, $subject);
                $prevArray = [];
            }

            // найдем статус в подготовленном к отправке массиве
            // если такой статус туда уже помещен, то дополним его пакетами
            // а если нет, то создадим новый
            $key = array_search($receiver['stateName'], array_column($prevArray, 'stateName'));
            if (false !== $key) {
                ArrayHelper::merge($prevArray[$key], $receiver['packages']);
            }
            else {
                $record = [
                    'stateName' => $receiver['stateName'],
                    'time' => $receiver['time'],
                    'packages' => $receiver['packages'],
                ];
                $prevArray[] = $record;
            }

            $currentReceiver = $receiver['receiver'];
        }

        // еще раз отправку для последней сборки пакетов
        if (count($prevArray) > 0) $this->sendEmailNotificationAboutOudatedByCustomTime($currentReceiver, $prevArray, $template, $subject);
    }

    /**
     * Запускается один раз в день утром, отправляет файл Excel, созданный по алгоритму отчета "Наличие проектов и задач".
     * mailing-pbx-calls-has-projects-and-tasks-assigned
     */
    public function actionMailingPbxCallsHasProjectsAndTasksAssigned()
    {
        $operatingDate = date('Y-m-d', time() - 24*60*60);

        $searchModel = new ReportPbxAnalytics();
        $dataProvider = $searchModel->searchCurrentTasks([
            $searchModel->formName() => [
                'searchPeriodEnd' => $operatingDate,
            ],
        ]);

        $savePath = Yii::getAlias('@uploads');
        if (!is_dir($savePath)) {
            if (!FileHelper::createDirectory($savePath)) return false;
        }
        $savePath = realpath($savePath);

        $fn = 'Наличие проектов и задач ' . Yii::$app->formatter->asDate($searchModel->searchPeriodEnd, 'php:d.m.Y') . '.xlsx';
        $ffp = $savePath . '/' . $fn;
        Excel::export([
            'models' => $dataProvider->getModels(),
            'asAttachment' => false,
            'savePath' => $savePath,
            'fileName' => $fn,
            'columns' => [
                [
                    'attribute' => 'pbxht_id',
                    'header' => $searchModel->attributeLabels()['pbxht_id'],
                ],
                [
                    'attribute' => 'pbxht_name',
                    'header' => $searchModel->attributeLabels()['pbxht_name'],
                ],
                [
                    'attribute' => 'pbxht_managerName',
                    'header' => $searchModel->attributeLabels()['pbxht_managerName'],
                ],
                [
                    'attribute' => 'pbxht_projectsInProgressCount',
                    'header' => $searchModel->attributeLabels()['pbxht_projectsInProgressCount'],
                ],
                [
                    'attribute' => 'pbxht_tasksCount',
                    'header' => $searchModel->attributeLabels()['pbxht_tasksCount'],
                ],
            ],
        ]);

        // отправим письмо с файлом
        try {
            Yii::$app->mailer->compose([
                'html' => 'pbxCallsHasProjectsAndTasksAssigned-html',
            ], [
                'operatingDate' => $operatingDate,
            ])
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderCompanyWaste']])
                ->setTo('bugrovap@gmail.com')
                ->setSubject('Незавершенные проекты и наличие задач по контрагентам на ' . Yii::$app->formatter->asDate($operatingDate, 'php:d.m.Y'))
                ->attach($ffp)
                ->send();
        }
        catch (\Exception $exception) {}

        // удалим файл после попытки отправить письмо: не важно успешной она была или нет
        if (file_exists($ffp)) unlink($ffp);
    }

    /**
     * Запускается один раз в пять минут, идентифицирует сайты у звонков, у которых они не идентифицированы.
     * identify-website-in-cdr
     */
    public function actionIdentifyWebsiteInCdr()
    {
        // делаем выборку номеров телефонов с сайтами, которым они принадлежат
        $knownPhones = ArrayHelper::map(pbxExternalPhoneNumber::find()->all(), 'phone_number', 'website_id');

        $calls = pbxCalls::find()
            ->where(['website_id' => null])
            ->andWhere(['not', ['userfield' => null]])
            ->andWhere('userfield <> ""')
            ->orderBy('calldate DESC')
            ->limit(100)
            ->all();
        foreach ($calls as $call) {
            if (is_numeric($call->userfield)) {
                $website = ArrayHelper::getValue($knownPhones, $call->userfield);
                if (!empty($website)) {
                    //print '<p>Для номера ' . $call->userfield . ' определен сайт ' . $website . '.</p>';
                    $call->updateAttributes([
                        'website_id' => $website,
                    ]);
                }
                unset($website);
            }
            //else print '<strong>Не число</strong>';
        }
    }

    /**
     * Исключает E-mail из рассылки уведомлений о состоянии почтовых отправлений.
     * unsubscribe-from-cp-notifications
     * @param $email string E-mail контактного лица контрагента, который необходимо исключить
     */
    public function actionUnsubscribeFromCpNotifications($email)
    {
        $model = CpBlContactEmails::findOne(['email' => trim($email)]);
        if (empty($model)) {
            $model = new CpBlContactEmails([
                'email' => trim($email),
            ]);

            $ca = Yii::$app->request->get('ca');
            if (null != $ca) {
                $ca = intval(Yii::$app->request->get('ca'));
                if ($ca > 0) $model->fo_ca_id = $ca;
            }

            if ($model->save()) {
                print '<p>Вы успешно отписались от рассылки!</p>';
            }
            else {
                print '<p>Отписаться от рассылки невозможно из-за ошибки в системе!</p>';
            }
        }
    }

    public function actionTemp()
    {
        /*
        // отправляем файл в бакет Yandex Cloud
        $call = PbxCalls::findOne(678818);
        if ($call) {
            $ffp = '';
            $recordsPath = '/mnt/voipnfs/' . Yii::$app->formatter->asDate($call->calldate, 'php:Y.m.d');
            foreach (glob("$recordsPath/*$call->uniqueid*") as $filename) {
                if (!empty($filename)) {
                    $ffp = $filename;
                    break;
                }
            }

            if (!empty($ffp) && file_exists($ffp)) {
                $result = Yii::$app->yandexCloud->upload($call->id . '.wav', $ffp);
                if (isset($result->toArray()['ObjectURL'])) {
                    echo $result->toArray()['ObjectURL'];
                }
            }
        }
        return false;
        */
    }

    /**
     * Делает выборку тендеров и анализирует изменения в них.
     * track-tenders-changes
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTrackTendersChanges()
    {
        $tenders = []; // здесь будем хранить массив файлов, которые необходимо включить в письмо

        // тендеры в финальных статусах не берем!
        foreach (Tenders::find()->joinWith('winner')->where(['not in', Tenders::tableName() . '.`state_id`', TendersStates::НАБОР_ФИНАЛЬНЫЕ_СТАТУСЫ])->all() as $tender) {
            $changes = [];
            $law_no = $tender->law_no;

            // проверяем изменения в общей информации
            $currentData = (new Tenders([
                'oos_number' => $tender->oos_number,
            ]))->fetchTenderByNumber($law_no);
            if (isset($currentData['pf_date_u'])) {
                $changes[] = [
                    'attributeName' => 'date_stop',
                    'newValue' => $currentData['pf_date_u'],
                    'oldFormatted' => Yii::$app->formatter->asDate($tender->date_stop, 'php:d.m.Y'),
                    'newFormatted' => Yii::$app->formatter->asDate($currentData['pf_date_u'], 'php:d.m.Y'),
                ];
            }

            // идентифицируем этап закупки
            if (!empty($currentData['stage'])) {
                $stage = TendersStages::findOne(['name' => $currentData['stage']]);
                if ($stage) {
                    $stage_id = $stage->id;
                }
                else {
                    $stage = new TendersStages(['name' => $currentData['stage']]);
                    if ($stage->save()) {
                        $stage_id = $stage->id;
                    }
                }
                if (!empty($stage_id)) {
                    $changes[] = [
                        'attributeName' => 'stage_id',
                        'newValue' => $stage_id,
                        'oldFormatted' => $tender->stageName,
                        'newFormatted' => trim($currentData['stage']),
                    ];
                }
            }

            // журналируем изменения
            $tender->logChangesCommon($changes);
            unset($changes);
            /*
            if ($currentData['pf_date_u'] != $tender->date_stop) {
                if ($tender->updateAttributes([
                    'date_stop' => $currentData['pf_date_u'],
                ]) > 0 ) {
                    // изменилась дата окончания приема заявок, сделаем запись в журнал об этом
                    (new TendersLogs([
                        'tender_id' => $tender->id,
                        'description' => 'Дата окончания приема заявок была изменена с ' . Yii::$app->formatter->asDate($currentData['pf_date_u'], 'php:d.m.Y') . ' на ' . Yii::$app->formatter->asDate($tender->date_stop, 'php:d.m.Y') . '.',
                    ]))->save();
                };
            }
            */

            // проверяем изменения в файлах
            // сначала со страницы "Документы"
            $law_no++;
            $currentData = (new Tenders([
                'oos_number' => $tender->oos_number,
            ]))->fetchTenderByNumber($law_no);
            // удалим ненужный элемент массива
            if (isset($currentData['law_no'])) {
                unset($currentData['law_no']);
            }

            // затем со страницы "Протоколы"
            if ($tender->law_no == 10) {
                // но только для 223го закона
                $currentVata = (new Tenders([
                    'oos_number' => $tender->oos_number,
                ]))->fetchTenderByNumber(Tenders::TENDERS_223_PAGE_PROTOCOLS);
                // удалим ненужный элемент массива
                if (isset($currentVata['law_no'])) {
                    unset($currentVata['law_no']);
                }
                $currentData = ArrayHelper::merge($currentData, $currentVata);
            }

            if (is_array($currentData) && count($currentData) > 0) {
                // перебираем файлы, выкачивая отсутствующие у нас в системе
                $files = $tender->obtainAuctionFiles($currentData, true);
                if (!empty($files)) {
                    $tenders[] = [
                        'tender_id' => $tender->id,
                        'oos_number' => $tender->oos_number,
                        'title' => $tender->title,
                        'files' => $files,
                    ];
                }
            }

            // удаляем имеющуюся историю, записываем текущую заново
            $law_no++;
            $currentData = (new Tenders([
                'oos_number' => $tender->oos_number,
            ]))->fetchTenderByNumber($law_no);
            if (isset($currentData['law_no'])) {
                unset($currentData['law_no']);
            }
            if (is_array($currentData) && count($currentData) > 0) {
                // удаляем все старые записи журнала событий
                TendersLogs::deleteAll(['tender_id' => $tender->id, 'type' => TendersLogs::TYPE_ИСТОЧНИК]);
                // извлекаем и сохраняем имеющиеся на странице записи
                $tender->obtainAuctionLogs($currentData);
            }

            // определим победителя в торгах
            if (empty($tender->winner)) {
                // если он не задан
                $law_no++;
                $currentData = (new Tenders([
                    'oos_number' => $tender->oos_number,
                ]))->fetchTenderByNumber($law_no); // 23
                if (isset($currentData['law_no'])) {
                    unset($currentData['law_no']);
                }
                if (is_array($currentData) && count($currentData) > 0) {
                    $tenderResults = TendersResults::findOne(['tender_id' => $tender->id]);
                    if ($tenderResults) {
                        $tenderResults->updateAttributes([
                            'placed_at' => $currentData['placed_at'],
                            'name' => $currentData['name'],
                            'price' => $currentData['price'],
                        ]);
                    } else {
                        (new TendersResults([
                            'tender_id' => $tender->id,
                            'placed_at' => $currentData['placed_at'],
                            'name' => $currentData['name'],
                            'price' => $currentData['price'],
                        ]))->save();
                    }
                }
            }
        }

        if (!empty($tenders)) {
            // если происходило обновление, то необходимо все новые файлы включить в письмо и выслать тендеристам
            $letter = Yii::$app->mailer->compose([
                'html' => 'tenderFilesHasBeenUpdated-html',
            ], ['tenders' => $tenders])
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo(['tender@1nok.ru', 'bugrovap@gmail.com'])
                ->setSubject('Изменения в тендерах');

            foreach ($tenders as $changedTender) {
                foreach ($changedTender['files'] as $file) {
                    $letter->attach($file['ffp'], ['fileName' => $file['fn']]);
                }
            }

            $letter->send();
        }
    }

    /**
     * Выполняет рассылку информации о предстоящих аукционах специалистам тендерного отдела.
     */
    public function actionDispatchOnTheEve()
    {
        $tenders = []; // здесь будем хранить массив файлов, которые необходимо включить в письмо
        $tomorrowStart = strtotime(date('Y-m-d', (time() + 24*3600)).' 00:00:00');
        $tomorrowEnd = strtotime(date('Y-m-d', (time() + 24*3600)).' 23:59:59');

        // тендеры в финальных статусах не берем!
        foreach (
            Tenders::find()
                ->where(Tenders::tableName() . '.`state_id` <= ' . TendersStates::STATE_ДОЗАПРОС)
                ->andWhere('date_auction IS NOT NULL')
                ->andWhere(['between', 'date_auction', $tomorrowStart, $tomorrowEnd])
                ->all() as $tender
        ) {
            $tenders[] = [
                'tender_id' => $tender->id,
                'oos_number' => $tender->oos_number,
                'title' => $tender->title,
            ];
        }

        if (!empty($tenders)) {
            Yii::$app->mailer->compose([
                'html' => 'tendersOnTheEve-html',
            ], ['tenders' => $tenders, 'date' => Yii::$app->formatter->asDate($tomorrowStart, 'php:d.m.Y')])
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo(['tender@1nok.ru', 'bugrovap@gmail.com'])
                ->setSubject('Предстоящие аукционы')
                ->send();
        }
    }

    /**
     * Проверяет готовность распознания поставленных в очередь записей разговоров.
     * check-transcribations
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionCheckTranscribations()
    {
        $operationsPending = YandexSpeechKitRecognitionQueue::find()->all();
        if (count($operationsPending) > 0) {
            $callsIds = ArrayHelper::getColumn($operationsPending, 'call_id');
            $calls = PbxCalls::find()->where(['id' => $callsIds])->all();

            $client = new \yii\httpclient\Client([
                'baseUrl' => YandexServices::URL_SPEECHKIT_RECOGNITION_RESULTS,
                'transport' => 'yii\httpclient\CurlTransport'
            ]);

            foreach ($operationsPending as $operationPending) {
                if (!empty($operationPending->operation_id)) {
                    $response = $client->createRequest()
                        ->setUrl($operationPending->operation_id)
                        ->setMethod('GET')
                        ->setHeaders([
                            'Authorization' => 'Api-Key ' . YandexServices::SPEECHKIT_API_KEY,
                        ])->send();

                    if ($response->isOk) {
                        // идентифицируем звонок
                        $key = array_search($operationPending->call_id, array_column($calls, 'id'));
                        if (
                            $key !== false &&
                            isset($response->data['done']) && $response->data['done'] == true &&
                            isset($response->data['response']) && isset($response->data['response']['chunks'])
                        ) {
                            // создаем файл, в которй будет производиться запись результата
                            $recognitionResult = '';
                            $pifp = YandexSpeechKitRecognitionQueue::getUploadsFilepath($calls[$key]);
                            $fileAttachedFn = strtolower(Yii::$app->security->generateRandomString() . '.txt');
                            $fileAttachedFfp = $pifp . '/' . $fileAttachedFn;
                            try {
                                $rrFile = fopen($fileAttachedFfp, 'a') or die("Unable to open file!");
                                foreach ($response->data['response']['chunks'] as $chunk) {
                                    $recognitionResult .= $chunk['alternatives'][0]['text'] . "\n";
                                }
                                $writeResult = fwrite($rrFile, $recognitionResult);
                                fclose($rrFile);
                                if (false !== $writeResult) {
                                    // делаем запись в таблицу успешно распознанных звонков
                                    if ((new PbxCallsRecognitions([
                                        'call_id' => $operationPending->call_id,
                                        'rr' => $recognitionResult,
                                        'ffp' => $fileAttachedFfp,
                                        'fn' => $fileAttachedFn,
                                    ]))->save()) {
                                        // удаляем из очереди
                                        $operationPending->delete();
                                        // удаляем сам файл из бакета
                                        $result = Yii::$app->yandexCloud->delete($operationPending->call_id . '.wav');
                                    }
                                };

                            } catch (\Exception $exception) {}

                            unset($recognitionResult);
                        }
                    }
                }
            }
        }
    }

    /**
     * Функция "Автоплатежи".
     * Выполняет создание платежных ордеров автоматически, по расписанию, на основании имеющихся шаблонов.
     * create-po-from-templates
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionCreatePoFromTemplates()
    {
        $today = date('d');
        foreach (PoAt::find()->where([
            'is_active' => true,
            'periodicity' => $today,
        ])->all() as $template) {
            $success = true;
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $model = new Po(['state_id' => PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ]);
                $model->attributes = $template->attributes;
                if ($model->save()) {
                    // сразу запись в историю
                    (new PoStatesHistory([
                        'po_id' => $model->id,
                        'state_id' => $model->state_id,
                        'description' => 'Автоплатеж',
                    ]))->save();

                    $properties = Json::decode($template->properties, true);
                    if (!empty($properties)) {
                        foreach ($properties as $row) {
                            if (!(new PoPop([
                                'po_id' => $model->id,
                                'ei_id' => $model->ei_id,
                                'property_id' => $row['property_id'],
                                'value_id' => $row['value_id'],
                            ]))->save()) {
                                $success = false;
                                break;
                            }
                        }
                    }
                }
                else {
                    $success = false;
                }
            }
            catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
            catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            $success ? $transaction->commit() : $transaction->rollBack();
        }
    }

    /**
     * Выполняет рассылку просроченных объектов.
     * mailing-outdated-objects-by-schedule
     */
    public function actionMailingOutdatedObjectsBySchedule()
    {
        // здесь будем собирать данные для вывода в тексте письма
        $letterContent = [];

        // доступные разделы учета для этого механизма уведомлений
        $sections = OutdatedObjectsReceivers::fetchSections();

        // делаем выборку разделов учета в разрезе получателей
        $sectionsReceivers = OutdatedObjectsReceivers::find()->select([
            'section',
            'receivers' => new Expression('GROUP_CONCAT(`receiver` SEPARATOR ",")'),
        ])->groupBy('section')->asArray()->all();

        // делаем выборку получателей в разрезе разделов учета
        $receiversSections = OutdatedObjectsReceivers::find()->select([
            'receiver',
            'sections' => new Expression('GROUP_CONCAT(`section` SEPARATOR ",")'),
        ])->groupBy('receiver')->asArray()->all();

        /**
         * 1 - проекты по экологии
         */
        $key = array_search(OutdatedObjectsReceivers::SECTION_ЭКО_ПРОЕКТЫ, array_column($sectionsReceivers, 'section'));
        if (false !== $key) {
            unset($key);
            $key = array_search(OutdatedObjectsReceivers::SECTION_ЭКО_ПРОЕКТЫ, array_column($sections, 'id'));
            if (false !== $key) {
                $section = $sections[$key];
                $name = $section['name'] . ' с нарушениями сроков завершения этапов'; // наименование раздела учета

                $searchModel = new EcoProjectsSearch;
                $query = $searchModel->search([
                    $searchModel->formName() => [
                        'searchProgress' => EcoProjectsSearch::FILTER_EXPIRED_FOR_MAILING,
                    ],
                ]);

                $sample = $query->getModels();
                if (count($sample) > 0) {
                    $items = [];

                    foreach ($sample as $record) {
                        $items[] =
                            $record->id .
                            ' &mdash; ' .
                            foProjects::downcounter(strtotime($record->currentMilestoneDatePlan . ' 00:00:00'), time(), true) . ' ' .
                            Html::a('открыть', Yii::$app->urlManager->createAbsoluteUrl(['/' . EcoProjectsController::ROOT_URL_FOR_MILESTONES_SORT_PAGING, 'id' => $record->id]), ['title' => 'Открыть проект по экологии']) . ' или ' .
                            Html::a('открыть локально', 'http://' . Yii::$app->params['serverLocalIp'] . Url::toRoute(['/' . EcoProjectsController::ROOT_URL_FOR_MILESTONES_SORT_PAGING, 'id' => $record->id]), ['title' => 'Если Вы работаете в одной сети с веб-приложением, нажмите здесь, чтобы открыть проект по экологии'])
                        ;
                    }

                    $letterContent[] = [
                        'id' => OutdatedObjectsReceivers::SECTION_ЭКО_ПРОЕКТЫ,
                        'name' => trim($name) . ' (' . count($items) . ' всего):',
                        'items' => $items,
                    ];
                    unset($name);
                }
            }
        }

        /**
         * 2 - договоры по экологии
         */
        $key = array_search(OutdatedObjectsReceivers::SECTION_ЭКО_ДОГОВОРЫ, array_column($sectionsReceivers, 'section'));
        if (false !== $key) {
            unset($key);
            $key = array_search(OutdatedObjectsReceivers::SECTION_ЭКО_ДОГОВОРЫ, array_column($sections, 'id'));
            if (false !== $key) {
                $section = $sections[$key];
                $name = $section['name']; // наименование раздела учета

                $query = EcoMcTp::find()->select([
                    'id' => EcoMcTp::tableName() . '.`mc_id`',
                    'report_id',
                    'reportName' => EcoReportsKinds::tableName() . '.`name`',
                    'date_deadline',
                ])->joinWith('report')->where(['date_fact' => null])->andWhere('DATE_FORMAT(NOW(), "%Y-%m-%d") > `date_deadline`')->orderBy(['id' => SORT_ASC, 'report_id' => SORT_ASC]);

                $sample = $query->asArray()->all();
                unset($query);

                if (count($sample) > 0) {
                    $items = [];

                    foreach ($sample as $record) {
                        $items[] =
                            'Договор № ' . $record['id'] .
                            ' - ' .
                            $record['reportName'] .
                            ' &mdash; ' .
                            foProjects::downcounter(strtotime($record['date_deadline'] . ' 00:00:00'), time(), true) . ' ' .
                            Html::a('открыть', Yii::$app->urlManager->createAbsoluteUrl(['/' . EcoContractsController::ROOT_URL_FOR_SORT_PAGING . '/update', 'id' => $record['id']]), ['title' => 'Открыть договор сопровождения']) . ' или ' .
                            Html::a('открыть локально', 'http://' . Yii::$app->params['serverLocalIp'] . Url::toRoute(['/' . EcoContractsController::ROOT_URL_FOR_SORT_PAGING . '/update', 'id' => $record['id']]), ['title' => 'Если Вы работаете в одной сети с веб-приложением, нажмите здесь, чтобы открыть договор сопровождения'])
                        ;
                    }

                    $letterContent[] = [
                        'id' => OutdatedObjectsReceivers::SECTION_ЭКО_ДОГОВОРЫ,
                        'name' => trim($name) . ' (' . count($items) . ' всего):',
                        'items' => $items,
                    ];
                    unset($name);
                }
            }
        }

        /**
         * 3 - запросы на транспорт
         */
        $key = array_search(OutdatedObjectsReceivers::SECTION_ЗАПРОСЫ_ТРАНСПОРТА, array_column($sectionsReceivers, 'section'));
        if (false !== $key) {
            unset($key);
            $key = array_search(OutdatedObjectsReceivers::SECTION_ЗАПРОСЫ_ТРАНСПОРТА, array_column($sections, 'id'));
            if (false !== $key) {
                $section = $sections[$key];
                $name = $section['name']; // наименование раздела учета

                $query = TransportRequests::find()->select([
                    'id' => TransportRequests::tableName() . '.`id`',
                    'customer_name' => TransportRequests::tableName() . '.`customer_name`',
                    'created_at',
                    'pending' => '(UNIX_TIMESTAMP() - `created_at`)',
                ])->where(['state_id' => TransportRequestsStates::STATE_ОБРАБАТЫВАЕТСЯ]);

                if (isset($section['time_limit'])) {
                    // задан срок, применяем его в тексте запроса
                    $query->andWhere('(UNIX_TIMESTAMP() - `created_at`) > ' . $section['time_limit'])->andWhere(['finished_at' => null]);
                    $name .= ', период пребывания без движения которых превысил ' . foProjects::downcounter($section['time_limit']);
                }
                $sample = $query->asArray()->all();
                unset($query);

                if (count($sample) > 0) {
                    $items = [];

                    foreach ($sample as $record) {
                        $items[] =
                            $record['id'] .
                            ' - ' .
                            $record['customer_name'] .
                            ' &mdash; ' .
                            foProjects::downcounter($record['created_at'], time(), true) . ' ' .
                            Html::a('открыть', Yii::$app->urlManager->createAbsoluteUrl(['/transport-requests/update', 'id' => $record['id']]), ['title' => 'Открыть запрос на транспорт']) . ' или ' .
                            Html::a('открыть локально', 'http://' . Yii::$app->params['serverLocalIp'] . Url::toRoute(['/transport-requests/update', 'id' => $record['id']]), ['title' => 'Если Вы работаете в одной сети с веб-приложением, нажмите здесь, чтобы открыть запрос на транспорт'])
                        ;
                    }

                    $letterContent[] = [
                        'id' => OutdatedObjectsReceivers::SECTION_ЗАПРОСЫ_ТРАНСПОРТА,
                        'name' => trim($name) . ' (' . count($items) . ' всего):',
                        'items' => $items,
                    ];
                    unset($name);
                }
            }
        }

        /**
         * 4 - пакеты корреспонденции
         */
        $key = array_search(OutdatedObjectsReceivers::SECTION_ПАКЕТЫ, array_column($sectionsReceivers, 'section'));
        if (false !== $key) {
            unset($key);
            $key = array_search(OutdatedObjectsReceivers::SECTION_ПАКЕТЫ, array_column($sections, 'id'));
            if (false !== $key) {
                $section = $sections[$key];
                $name = $section['name']; // наименование раздела учета

                $query = CorrespondencePackages::find()->select([
                    'id' => CorrespondencePackages::tableName() . '.`id`',
                    'customer_name' => CorrespondencePackages::tableName() . '.`customer_name`',
                    'ready_at',
                    'pending' => '(UNIX_TIMESTAMP() - `ready_at`)',
                ])->where(['state_id' => ProjectsStates::STATE_ОЖИДАЕТ_ОТПРАВКИ])->orderBy(['pending' => SORT_DESC]);

                if (isset($section['time_limit'])) {
                    // задан срок, применяем его в тексте запроса
                    $query->andWhere('(UNIX_TIMESTAMP() - `ready_at`) > ' . $section['time_limit'])->andWhere(['sent_at' => null]);
                    $name .= ', ожидающие отправки более ' . foProjects::downcounter($section['time_limit']);
                }
                $sample = $query->asArray()->all();
                unset($query);

                if (count($sample) > 0) {
                    $items = [];

                    foreach ($sample as $record) {
                        $items[] =
                            $record['id'] .
                            ' - ' .
                            $record['customer_name'] .
                            ' &mdash; ' .
                            foProjects::downcounter($record['ready_at'], time(), true) . ' ' .
                            Html::a('открыть', Yii::$app->urlManager->createAbsoluteUrl(['/correspondence-packages/update', 'id' => $record['id']]), ['title' => 'Открыть пакет корреспонденции']) . ' или ' .
                            Html::a('открыть локально', 'http://' . Yii::$app->params['serverLocalIp'] . Url::toRoute(['/correspondence-packages/update', 'id' => $record['id']]), ['title' => 'Если Вы работаете в одной сети с веб-приложением, нажмите здесь, чтобы открыть пакет корреспонденции'])
                        ;
                    }

                    $letterContent[] = [
                        'id' => OutdatedObjectsReceivers::SECTION_ПАКЕТЫ,
                        'name' => trim($name) . ' (' . count($items) . ' всего):',
                        'items' => $items,
                    ];
                    unset($name);
                }
            }
        }

        if (count($letterContent) > 0) {
            foreach ($receiversSections as $receiver) {
                $outdatedObjects = [];
                if (!empty($receiver['sections'])) {
                    foreach (explode(',', $receiver['sections']) as $receiverSection) {
                        $key = array_search($receiverSection, array_column($letterContent, 'id'));
                        if (false !== $key) {
                            $outdatedObjects[] = $letterContent[$key];
                        }
                        unset($key);
                    }
                }

                Yii::$app->mailer->compose([
                    'html' => 'outdatedObjectsBySchedule-html',
                ], ['outdatedObjects' => $letterContent])
                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderOutdatedObjects']])
                    ->setSubject('Просроченные объекты, которые требуют внимания')
                    ->setTo($receiver['receiver'])
                    ->send();
            }
        }
    }
}
