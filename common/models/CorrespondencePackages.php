<?php

namespace common\models;

use Yii;
use yii\data\ArrayDataProvider;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "correspondence_packages".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $is_manual Признак создания вручную
 * @property int $cps_id Статус пакета корреспонденции
 * @property int $ready_at Дата и время подготовки
 * @property int $sent_at Дата и время отправки
 * @property int $delivered_at Дата и время доставки
 * @property int $paid_at Дата и время оплаты
 * @property int $fo_project_id ID проекта
 * @property int $fo_id_company Контрагент из Fresh Office
 * @property string $customer_name Контрагент
 * @property int $state_id Статус
 * @property int $type_id Тип
 * @property string $pad Виды документов
 * @property int $pd_id Способ доставки
 * @property int $pochta_ru_order_id Номер заказа (оправления) на Почте России
 * @property string $track_num Трек-номер
 * @property int $address_id Адрес почтовый
 * @property string $other Другие документы
 * @property string $comment Примечание
 * @property int $manager_id Ответственный
 * @property int $fo_contact_id Контактное лицо из CRM
 * @property string $contact_person Контактное лицо
 * @property string $contact_email E-mail для уведомлений о состоянии почтового отправления
 * @property int $rejects_count Количество отказов по пакету
 * @property int $delivery_notified_at Дата и время отправки уведомления контактному лицу о поступлении почтового отправления в отделение
 *
 * @property string $cpsName
 * @property int $lastCpsChangedAt
 * @property string $stateName
 * @property string $typeName
 * @property string $pdName
 * @property string $addressValue
 * @property string $managerProfileName
 *
 * @property foProjects $project
 * @property PostDeliveryKinds $pd
 * @property ProjectsStates $state
 * @property ProjectsTypes $type
 * @property User $manager
 * @property AuthAssignment $managerRole
 * @property Profile $managerProfile
 * @property CounteragentsPostAddresses $address
 * @property CorrespondencePackagesStates $cps
 * @property CpBlContactEmails $canUnsubscribe
 * @property CorrespondencePackagesFiles[] $correspondencePackagesFiles
 * @property CorrespondencePackagesHistory[] $correspondencePackagesHistory
 * @property Edf $edf
 * @property PoStatesHistory $lastCpsChanged
 * @property Edf[] $edfs
 */
class CorrespondencePackages extends \yii\db\ActiveRecord
{
    /**
     * Формы уведомлений о состоянии почтовых отправлений
     */
    const NF_PRIMARY = 1; // первичное уведомление (о том, что посылка была отправлена)
    const NF_ARRIVED = 2; // уведомление о том, что посылка прибыла в почтовое отделение

    /**
     * Шаблон для поля "Трек-номер" формы
     */
    const FORM_FIELD_TRACK_TEMPLATE = "{label}\n<div class=\"input-group\">\n{input}\n<span class=\"input-group-btn\"><button class=\"btn btn-default\" type=\"button\" id=\"btnTrackNumber\"><i class=\"fa fa-search\" aria-hidden=\"true\"></i> Отследить</button></span></div>\n{error}";

    /**
     * Табличная часть предоставленных видов документов.
     * @var array
     */
    public $tpPad;

    /**
     * @var string виртуальное поля для ввода причины при отказе менеджером
     */
    public $rejectReason;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'correspondence_packages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['state_id'], 'required'],
            // для создания пакета корреспонденции вручную необходимо обязательно выбрать контрагента
            [['fo_id_company', 'manager_id'], 'required', 'on' => 'manual_creating'],
            // для одобрения менеджером обязательно нужно выбрать способ и адрес доставки, контактное лицо и E-mail для уведомлений
            [['pd_id', 'address_id', 'fo_contact_id'], 'required', 'on' => 'manager_approving'],
            [['created_at', 'cps_id', 'ready_at', 'sent_at', 'delivered_at', 'paid_at', 'fo_project_id', 'fo_id_company', 'state_id', 'type_id', 'pd_id', 'pochta_ru_order_id', 'address_id', 'manager_id', 'fo_contact_id', 'rejects_count', 'delivery_notified_at'], 'integer'],
            ['is_manual', 'boolean'],
            [['pad', 'other', 'comment'], 'string'],
            [['customer_name', 'contact_email'], 'string', 'max' => 255],
            [['contact_email'], 'trim'],
            [['contact_email'], 'email'],
            [['track_num'], 'string', 'max' => 50],
            [['contact_person'], 'string', 'max' => 100],
            [['track_num'], 'trim'],
            [['customer_name', 'track_num', 'other', 'comment', 'contact_person', 'contact_email'], 'default', 'value' => null],
            [['cps_id'], 'exist', 'skipOnError' => true, 'targetClass' => CorrespondencePackagesStates::className(), 'targetAttribute' => ['cps_id' => 'id']],
            [['address_id'], 'exist', 'skipOnError' => true, 'targetClass' => CounteragentsPostAddresses::className(), 'targetAttribute' => ['address_id' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['manager_id' => 'id']],
            [['pd_id'], 'exist', 'skipOnError' => true, 'targetClass' => PostDeliveryKinds::className(), 'targetAttribute' => ['pd_id' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectsStates::className(), 'targetAttribute' => ['state_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectsTypes::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['tpPad', 'rejectReason'], 'safe'],
            // собственные правила валидации
            ['cps_id', 'validatePackageState'],
            ['track_num', 'validateTrackNumber', 'skipOnEmpty' => false],
            ['pd_id', 'validateDeliveryMethod'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'is_manual' => 'Признак создания вручную',
            'cps_id' => 'Статус пакета корреспонденции',
            'ready_at' => 'Дата и время подготовки',
            'sent_at' => 'Дата и время отправки',
            'delivered_at' => 'Дата и время доставки',
            'paid_at' => 'Дата и время оплаты',
            'fo_project_id' => 'ID проекта',
            'fo_id_company' => 'Контрагент',
            'customer_name' => 'Контрагент',
            'state_id' => 'Статус',
            'type_id' => 'Тип',
            'pad' => 'Виды документов',
            'pd_id' => 'Способ доставки',
            'pochta_ru_order_id' => '№ заказа Почты РФ', // Номер заказа (оправления) на Почте России
            'track_num' => 'Трек-номер',
            'address_id' => 'Адрес почтовый',
            'other' => 'Другие документы',
            'comment' => 'Примечание',
            'manager_id' => 'Ответственный',
            'fo_contact_id' => 'Контактное лицо',
            'contact_person' => 'Контактное лицо',
            'contact_email' => 'E-mail для уведомлений о состоянии почтового отправления',
            'rejects_count' => 'Количество отказов по пакету',
            'delivery_notified_at' => 'Дата и время отправки уведомления контактному лицу о поступлении почтового отправления в отделение',
            'rejectReason' => 'Причина отказа',
            // вычисляемые поля
            'cpsName' => 'Статус пакета',
            'stateName' => 'Статус',
            'typeName' => 'Тип',
            'pdName' => 'Способ доставки',
            'managerProfileName' => 'Ответственный',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    /**
     * Отсылает на E-mail контактного лица контрагента уведомление об отправке ему пакета корреспонденции.
     * @param $type integer форма уведомления
     * @return bool
     */
    public function sendClientNotification($type)
    {
        if (!empty(trim($this->contact_email))) {
            if (CpBlContactEmails::find()->select('email')->where(['email' => $this->contact_email])->count() == 0) {
                // отправляем, только если E-mail не находится в блок-листе
                switch ($type) {
                    case self::NF_PRIMARY:
                        $form = 'cpHasBeenSent-html';
                        $subject = 'Вам отправлен пакет корреспонденции';
                        break;
                    case self::NF_ARRIVED:
                        $form = 'cpHasBeenArrived-html';
                        $subject = 'Отправленный вам пакет корреспонденции ожидает в почтовом отделении';
                        break;
                }

                if (!empty($form)) {
                    $letter = Yii::$app->mailer->compose([
                        'html' => $form,
                    ], [
                        'model' => $this,
                    ])->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameVictor']])
                        ->setSubject($subject)
                        //->setCc('bugrovap@gmail.com')
                        ->setTo($this->contact_email);

                    return $letter->send();
                }
            }
        }

        return true;
    }

    /**
     * Отправляет менеджеру на почту уведомление о том, что был создан новый или восстановлен из пепла некий пакет.
     * @param $subject string тема письма
     * @param $body string текст уведомления
     */
    public function sendManagerNotification($subject = 'Создан пакет корреспонденции', $body = 'Создан пакет корреспонденции%CA_NAME%, в котором Вы указаны как ответственный.')
    {
        if ($this->is_manual && $this->manager != null && $this->managerProfile != null &&
            $this->managerProfile->notify_when_cp == true) {
            if (!empty($this->customer_name)) {
                $body = str_replace('%CA_NAME%', ' по контрагенту <strong>' . $this->customer_name . '</strong>', $body);
            }
            else {
                $body = str_replace('%CA_NAME%', '', $body);
            }

            // если установлен признак необходимости отправки E-mail-уведомления менеджеру, то сделаем это
            $letter = Yii::$app->mailer->compose([
                'html' => 'newCorrespondencePackageHasBeenCreated-ForManager-html',
            ], [
                'model' => $this,
                'body' => $body,
            ])->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameVictor']])
                ->setSubject($subject)
                ->setTo($this->manager->email);

            $letter->send();
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением объекта

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = CorrespondencePackagesFiles::find()->where(['cp_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            // удаляем историю изменения статусов
            CorrespondencePackagesHistory::deleteAll(['cp_id' => $this->id]);

            // открепляем возможную ссылку в документообороте
            Edf::updateAll([
                'cp_id' => null,
            ], [
                'cp_id' => $this->id,
            ]);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->is_manual && $this->fo_contact_id != null) {
                $contactPerson = DirectMSSQLQueries::fetchContactPerson($this->fo_contact_id);
                if (count($contactPerson) > 0) {
                    $name = trim($contactPerson[0]['name']);
                    $phones = trim($contactPerson[0]['phones'], ', ');
                    $phones = trim($phones);
                    if ($name != '') $phones = ' (' . $phones . ')';
                    $this->contact_person = $name . $phones;
                }
            }

            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            // только для новых
            $this->sendManagerNotification();
        }

        if (isset($changedAttributes['cps_id'])) {
            // проверим, изменился ли статус пакета корреспонденции
            if ($changedAttributes['cps_id'] != $this->cps_id) {
                $rejectReason = null;
                // статус изменился, сделаем запись в историю об этом
                switch ($this->cps_id) {
                    /*
                    // отменено по просьбе заказчика
                    case CorrespondencePackagesStates::STATE_ЧЕРНОВИК:
                        // если пакет переводится из статуса Отказ, то отправим дополнительное письмо
                        if ($changedAttributes['cps_id'] == CorrespondencePackagesStates::STATE_ОТКАЗ) {
                            $this->sendManagerNotification('Отказной пакет был восстановлен', 'Пакет корреспонденции, ранее находившийся в отказе, был переведен в статус &laquo;Черновик&raquo;.');
                        }

                        break;
                    */
                    case CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ:
                        if ($changedAttributes['cps_id'] == CorrespondencePackagesStates::STATE_ЧЕРНОВИК) {
                            $this->sendManagerNotification('На согласование поступил пакет документов', 'На согласование поступил пакет корреспонденции%CA_NAME%, в котором Вы указаны как ответственный.');
                        }
                    case CorrespondencePackagesStates::STATE_ОТКАЗ:
                    case CorrespondencePackagesStates::STATE_УТВЕРЖДЕН:
                        $oldStateName = '';
                        $oldState = CorrespondencePackagesStates::findOne($changedAttributes['cps_id']);
                        if ($oldState != null) $oldStateName = ' с ' . $oldState->name;

                        $cpHistoryModel = new CorrespondencePackagesHistory([
                            'created_by' => Yii::$app->user->id,
                            'cp_id' => $this->id,
                            'description' => 'Изменение статуса' . $oldStateName . ' на ' . $this->cpsName .
                                ($this->rejectReason == null ? '.' : '. Причина отказа: ' . $this->rejectReason),
                        ]);
                        $cpHistoryModel->save();
                        break;
                }

                if ($this->ready_at == null && ($this->cps_id == CorrespondencePackagesStates::STATE_ОТКАЗ || $this->cps_id == CorrespondencePackagesStates::STATE_УТВЕРЖДЕН)) {
                    $this->updateAttributes([
                        'ready_at' => time(),
                    ]);
                }

                if ($this->cps_id == CorrespondencePackagesStates::STATE_ОТКАЗ) {
                    $this->updateAttributes([
                        'rejects_count' => $this->rejects_count++,
                    ]);
                }
            }
        }

        if (isset($changedAttributes['state_id'])) {
            // проверим, изменился ли статус проекта в пакете корреспонденции
            if ($changedAttributes['state_id'] != $this->state_id) {
                $escapeImmediately = false;
                $edfStateId = null; // при необходимости проставим статус Отправлено или Доставлено также в соответствующих связанных электронных документах

                // статус отличается, зафиксируем время назначения некоторых статусов
                switch ($this->state_id) {
                    case ProjectsStates::STATE_ОЖИДАЕТ_ОТПРАВКИ:
                        $this->updateAttributes([
                            'ready_at' => time(),
                        ]);

                        break;
                    case ProjectsStates::STATE_ОТПРАВЛЕНО:
                        $this->updateAttributes([
                            'sent_at' => time(),
                        ]);

                        $edfStateId = EdfStates::STATE_ОТПРАВЛЕН;

                        // отправим клиенту уведомление о том, что ему был отправлен пакет корреспонденции
                        if (!empty($this->track_num) && $this->pd_id == PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ) $this->sendClientNotification(CorrespondencePackages::NF_PRIMARY);

                        break;
                    case ProjectsStates::STATE_ДОСТАВЛЕНО:
                        $edfStateId = EdfStates::STATE_ДОСТАВЛЕН;

                        // фиксируем время доставки
                        if ($this->delivered_at == null) {
                            $this->updateAttributes([
                                'delivered_at' => time(),
                            ]);
                        }

                        // переводим проект сразу в статус Завершено при определенных условиях
                        // не применяется для пакетов, созданных вручную (поскольку в них нет проектов)
                        if (!$this->is_manual && $this->pd_id == PostDeliveryKinds::DELIVERY_KIND_КУРЬЕР || $this->pd_id == PostDeliveryKinds::DELIVERY_KIND_САМОВЫВОЗ && $this->fo_project_id != null) {
                            // делаем сразу две записи в истории изменения статусов проекта
                            $this->project->ID_PRIZNAK_PROJECT = ProjectsStates::STATE_ДОСТАВЛЕНО;
                            $this->project->save();

                            // проверим, есть ли финансы. если их не окажется, то закрывать проект не будем
                            $project = DirectMSSQLQueries::fetchProjectsData($this->fo_project_id);
                            if (count($project) > 0 && $project['finance_count'] > 0) {
                                // текущий пакет сразу в статус Завершено. это же действие сделает запись и в истории тоже
                                $this->state_id = ProjectsStates::STATE_ИСПОЛНЕН;
                                $this->save(false);
                            }

                            $escapeImmediately = true;
                        }

                        break;
                }

                if (!empty($edfStateId)) {
                    $edfModel = Edf::findOne($this->id);
                    if ($edfModel) {
                        $edfModel->state_id = $edfStateId;
                        $edfModel->save(false); // чтобы выполнить также afterSave
                    }
                }

                // уходим отсюда вообще
                if ($escapeImmediately) return true;

                // если изменился статус проекта, то меняем его и в CRM
                if ($this->fo_project_id != null) {
                    // для ручных поле проекта не заполняется, поэтому сюда оно никогда не зайдет
                    $historyModel = foProjects::findOne(['ID_LIST_PROJECT_COMPANY' => $this->fo_project_id]);
                    if ($historyModel != null) {
                        $historyModel->ID_PRIZNAK_PROJECT = $this->state_id;
                        $historyModel->save();
                    }
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function validatePackageState()
    {
        if ($this->cps_id == CorrespondencePackagesStates::STATE_ОТКАЗ && $this->rejectReason == null)
            $this->addError('rejectReason', 'Заполните причину отказа.');
    }

    /**
     * @inheritdoc
     */
    public function validateTrackNumber()
    {
        if (($this->pd_id == PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ || $this->pd_id == PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS) && $this->state_id == ProjectsStates::STATE_ОТПРАВЛЕНО) {
            if (empty(trim($this->track_num)))
                $this->addError('track_num', 'Поле обязательно для заполнения при выбранном способе.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateDeliveryMethod()
    {
        if (
            $this->pd_id == PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS &&
            $this->cps_id != CorrespondencePackagesStates::STATE_ОТКАЗ &&
            $this->cps_id != CorrespondencePackagesStates::STATE_УТВЕРЖДЕН
            ) {
            if (!Yii::$app->user->can('root')) {
                $limit = Yii::$app->user->identity->profile->limit_cp_me;
                if ($limit != null) {
                    $from = strtotime(date('Y') . '-' . date('m') . '-01');
                    $packagesCount = CorrespondencePackages::find()
                        ->where('created_at >= ' . $from)
                        ->andWhere(['cps_id' => CorrespondencePackagesStates::STATE_УТВЕРЖДЕН])
                        ->andWhere(['pd_id' => PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS])
                        ->andWhere(['manager_id' => Yii::$app->user->id])
                        ->count();
                    if ($packagesCount >= $limit) $this->addError('pd_id', 'Достигнут лимит отправок этим способом.');
                }
            }
        }

        /*
        if ($this->pd_id == PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ && empty($this->contact_email)) {
            $this->addError('contact_email', 'Для данного способа доставки необходимо указать E-mail для уведомлений.');
        }
        */
    }

    /**
     * Берет актуальные на момент исполнения виды документов. Проставляет галочки в тех из них, которые отметил
     * пользователь (отмеченные пользователем находятся в виртуальном поле tpPad).
     * @return array
     */
    public function convertPadTableToArray()
    {
        $padKinds = PadKinds::find()->select(['id', 'name', 'name_full', 'is_provided' => new Expression(0)])->orderBy('name_full')->asArray()->all();

        if (is_array($this->tpPad) && count($this->tpPad) > 0)
            foreach ($this->tpPad as $index => $document) {
                $key = array_search($index, array_column($padKinds, 'id'));
                if (false !== $key) $padKinds[$key]['is_provided'] = true;
            }

        return json_encode($padKinds);
    }

    /**
     * Выполняет конвертацию данных из поля pad текущей модели и преобразует их в объект ArrayDataProvider.
     * @return ArrayDataProvider
     */
    public function convertPadToDataProvider()
    {
        return new ArrayDataProvider([
            //'modelClass' => 'common\models\ReportAnalytics',
            'allModels' => json_decode($this->pad),
            //'key' => 'table1_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
//                'defaultOrder' => ['table1_count' => SORT_DESC],
//                'attributes' => [
//                    'table1_id',
//                    'table1_name',
//                    'table1_count',
//                ],
            ],
        ]);
    }

    /**
     * Выполняет создание задачи для менеджера через API Fresh Office.
     * @param $type_id integer идентификатор типа задачи
     * @param $ca_id integer идентификатор контрагента, который привязывается к задаче
     * @param $receiver_id integer идентификатор менеджера (ответственного лица)
     * @param $note string текст задачи
     * @return array|integer|bool
     */
    public function foapi_createNewTaskForManager($type_id, $ca_id, $receiver_id, $note)
    {
        $params = [
            'company_id' => $ca_id,
            'user_id' => $receiver_id,
            'category_id' => FreshOfficeAPI::TASK_CATEGORY_СТАНДАРТНАЯ,
            'status_id' => FreshOfficeAPI::TASKS_STATUS_ЗАПЛАНИРОВАН,
            'type_id' => $type_id,
            'date_from' => date('Y-m-d\TH:i:s.u', time()),
            'date_till' => date('Y-m-d\TH:i:s.u', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"))),
            'note' => $note,
        ];

        $response = FreshOfficeAPI::makePostRequestToApi('tasks', $params);
        // проанализируем результат, который возвращает API Fresh Office
        $decoded_response = json_decode($response, true);
        if (isset($decoded_response['error'])) {
            $inner_message = '';
            if (isset($decoded_response['error']['innererror']))
                $inner_message = ' ' . $decoded_response['error']['innererror']['message'];
            // возникла ошибка при выполнении
            return 'При создании задачи возникла ошибка: ' . $decoded_response['error']['message']['value'] . $inner_message;
        }
        elseif (isset($decoded_response['d']))
            // фиксируем идентификатор задачи, которая была успешно создана
            return $decoded_response['d']['id'];

        return false;
    }

    /**
     * Рендерит необходимые кнопки для управления пакетом корреспонденции в зависимости от его статуса и роли пользователя.
     * @return mixed
     */
    public function renderSubmitButtons()
    {
        $result = '';

        $siaButtons = [
            'save' => Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']),
            'rollback' => Html::submitButton('<i class="fa fa-refresh" aria-hidden="true"></i> Вернуть в черновики', ['class' => 'btn btn-warning btn-lg', 'name' => 'rollback', 'title' => 'Вернуть пакет в черновики, чтобы исправить ошибки']),
        ];

        if ($this->is_manual) {
            // наборы кнопок для пакетов, созданных вручную
            if (Yii::$app->user->can('operator_head') || Yii::$app->user->can('root'))
                // набор для Старшего оператора (он же инициатор создания пакета) и Полных прав
                switch ($this->cps_id) {
                    case CorrespondencePackagesStates::STATE_ЧЕРНОВИК:
                        $result .= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить черновик', ['class' => 'btn btn-primary btn-lg']) . ' ' .
                            Html::submitButton('Отправить на согласование <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'order_ready', 'title' => 'Отправить менеджеру на согласование']);
                        break;
                    case CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ:
                        $result .= Html::button('<i class="fa fa-spinner fa-pulse fa-fw"></i><span class="sr-only">Согласование...</span> Ожидайте согласования...', ['class' => 'btn btn-default btn-lg disabled']);
                        break;
                    case CorrespondencePackagesStates::STATE_УТВЕРЖДЕН:
                        $result .= $siaButtons['save'] . ' ' . $siaButtons['rollback'];
                        break;
                    case CorrespondencePackagesStates::STATE_ОТКАЗ:
                        $result .= $siaButtons['rollback'];
                        break;
                }

            if (Yii::$app->user->can('dpc_head') || Yii::$app->user->can('sales_department_manager') || Yii::$app->user->can('ecologist') || Yii::$app->user->can('ecologist_head') || Yii::$app->user->can('root'))
                // набор для Менеджера и Полных прав
                switch ($this->cps_id) {
                    case CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ:
                        $result .= Html::submitButton('<i class="fa fa-check" aria-hidden="true"></i> Согласовать', ['class' => 'btn btn-success btn-lg', 'name' => 'order_approve', 'title' => 'Согласовать и сразу отправить на оплату']) . ' ' .
                            Html::submitButton('<i class="fa fa-times" aria-hidden="true"></i> Отказать', ['class' => 'btn btn-danger btn-lg', 'name' => 'order_reject', 'title' => 'Отказать в согласовании (обязательно нужно будет указать причину согласования)']);
                        break;
                    case CorrespondencePackagesStates::STATE_УТВЕРЖДЕН:
                        // операторы попросили убрать возможность отзыва согласованного пакета
                        //$result .= Html::submitButton('<i class="fa fa-times" aria-hidden="true"></i> Отозвать', ['class' => 'btn btn-warning btn-lg', 'name' => 'order_cancel', 'title' => 'Отменить согласование']);
                        break;
                }
        }
        else {
            // наборы кнопок для пакетов, затянутых из CRM автоматически по расписанию
            if ($this->isNewRecord)
                $result .= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']);
            else
                $result .= $siaButtons['save'];
        }

        return $result;
    }

    /**
     * Делает выборку почтовых адресов текущего контрагента и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public function arrayMapOfAddressesForSelect2()
    {
        return ArrayHelper::map(CounteragentsPostAddresses::findAll(['counteragent_id' => $this->fo_id_company]), 'id', 'src_address');
    }

    /**
     * Делает выборку контактных лиц текущего контрагента и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public function arrayMapOfContactPersonsForSelect2()
    {
        $contactPersons = DirectMSSQLQueries::fetchCounteragentsContactPersons($this->fo_id_company);
        if (count($contactPersons) > 0) return ArrayHelper::map($contactPersons, 'id', 'text');

        return [];
    }

    /**
     * Делает выборку E-mail'ов текущего контрагента и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public function arrayMapOfCompanyEmailsForSelect2()
    {
        // делаем выборку почтовых ящиков контрагента
        $query = foListEmailClient::find()->select('email')->where(['ID_COMPANY' => $this->fo_id_company])->asArray()->column();
        // проверяем каждый отдельный ящик на присутствие в блок-листе (список отказавшихся от рассылки)
        foreach (CpBlContactEmails::find()->select('email')->where(['email' => $query])->asArray()->column() as $exclusion) {
            $key = array_search($exclusion, $query);
            if (false !== $key) {
                unset($query[$key]);
            }
        }

        if (empty($query)) return ['']; else return $query;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCps()
    {
        return $this->hasOne(CorrespondencePackagesStates::className(), ['id' => 'cps_id']);
    }

    /**
     * Возвращает наименование статуса пакета корреспонденции.
     * @return string
     */
    public function getCpsName()
    {
        return $this->cps != null ? $this->cps->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(foProjects::className(), ['ID_LIST_PROJECT_COMPANY' => 'fo_project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(ProjectsStates::className(), ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса проекта.
     * @return string
     */
    public function getStateName()
    {
        return $this->state != null ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(ProjectsTypes::className(), ['id' => 'type_id']);
    }

    /**
     * Возвращает наименование типа проекта.
     * @return string
     */
    public function getTypeName()
    {
        return $this->type != null ? $this->type->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPd()
    {
        return $this->hasOne(PostDeliveryKinds::className(), ['id' => 'pd_id']);
    }

    /**
     * Возвращает наименование способа доставки корреспонденции.
     * @return string
     */
    public function getPdName()
    {
        return $this->pd != null ? $this->pd->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagerProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'manager_id'])->from(['managerProfile' => 'profile']);
    }

    /**
     * Возвращает имя ответственного по контрагенту.
     * @return string
     */
    public function getManagerProfileName()
    {
        return !empty($this->managerProfile) ? (!empty($this->managerProfile->name) ? $this->managerProfile->name : $this->manager->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagerRole()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(CounteragentsPostAddresses::className(), ['id' => 'address_id']);
    }

    /**
     * Возвращает собственно адрес.
     * @return string
     */
    public function getAddressValue()
    {
        return $this->address != null ? $this->address->address_m : '';
    }

    /**
     * Возвращает возможный E-mail из списка исключаемых из рассылки уведомлений о почтовых отправлениях.
     * @return \yii\db\ActiveQuery
     */
    public function getCanUnsubscribe()
    {
        return $this->hasOne(CpBlContactEmails::className(), ['email' => 'contact_email']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorrespondencePackagesFiles()
    {
        return $this->hasMany(CorrespondencePackagesFiles::className(), ['cp_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorrespondencePackagesHistory()
    {
        return $this->hasMany(CorrespondencePackagesHistory::className(), ['cp_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastCpsChange()
    {
        return $this->hasOne(CorrespondencePackagesHistory::class, ['cp_id' => 'id'])->orderBy([CorrespondencePackagesHistory::tableName() . '.`created_at`' => SORT_DESC]);
    }

    /**
     * Возвращает дату и время последнего изменения статуса.
     * @return int
     */
    public function getLastCpsChangedAt()
    {
        return !empty($this->lastCpsChange) ? $this->lastCpsChange->created_at : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdf()
    {
        return $this->hasOne(Edf::className(), ['cp_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdfs()
    {
        return $this->hasMany(Edf::className(), ['cp_id' => 'id']);
    }
}
