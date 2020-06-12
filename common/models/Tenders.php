<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\httpclient\Client;
use polucorus\simplehtmldom\SimpleHTMLDom;
use backend\controllers\TendersController;

/**
 * This is the model class for table "tenders".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $tk_id Вид конкурса
 * @property string $law_no Номер закона, по которому осуществляется закупка
 * @property string $oos_number Номер закупки
 * @property string $revision Редакция заявки
 * @property int $state_id Статус внутренний
 * @property int $stage_id Этап закупки
 * @property string $title Наименование закупки
 * @property int $org_id Организация
 * @property int $fo_ca_id Контрагент
 * @property string $fo_ca_name Наименование контрагента из Fresh office
 * @property int $tp_id Тендерная площадка
 * @property int $manager_id Ответственный менеджер
 * @property int $responsible_id Исполнитель
 * @property string $conditions Особые требования
 * @property int $placed_at Дата размещения извещения
 * @property string $date_complete Срок выполнения работ (услуг)
 * @property int $date_stop Дата окончания приема заявок
 * @property int $date_sumup Дата подведения итогов
 * @property int $date_auction Дата проведения аукциона
 * @property int $ta_id Форма подачи
 * @property int $is_notary_required Требуется ли нотариальное заверение документов
 * @property int $is_contract_edit Возможно ли внесение изменений в текст договора
 * @property string $amount_start Начальная максимальная цена
 * @property string $amount_offer Наше ценовое предложение
 * @property string $amount_fo Сумма обеспечения заявки (order funding)
 * @property string $amount_fc Сумма обеспечения договора (contract funding)
 * @property int $deferral Срок оплаты (количество дней отсрочки платежа)
 * @property int $is_contract_approved Договор согласован (0 - нет, 1 - да, 2 - на согласовании)
 * @property int $complexity Сложность (диапазон 1-3)
 * @property int $lr_id Запрос лицензий
 * @property string $contract_comments Изменения в договоре
 * @property string $comment Комментарий
 *
 * @property string $createdByEmail
 * @property string $createdByProfileName
 * @property string $createdByProfileEmail
 * @property string $tkName
 * @property string $lawName
 * @property string $stateName
 * @property string $stageName
 * @property string $orgName
 * @property string $responsibleProfileName
 * @property string $managerProfileName
 * @property string $lossReasonName
 * @property array $tendersSourceIdsFiles
 *
 * @property TendersKinds $tk
 * @property TendersApplications $ta
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property AuthAssignment $createdByRole
 * @property User $manager
 * @property AuthAssignment $managerRole
 * @property Profile $managerProfile
 * @property Organizations $org
 * @property User $responsible
 * @property Profile $responsibleProfile
 * @property TendersStates $state
 * @property TendersStages $stage
 * @property TendersPlatforms $tp
 * @property LicensesRequests $lr
 * @property TendersLogs[] $tendersLogs
 * @property TendersTp[] $tendersTp
 * @property TendersFiles[] $tendersFiles
 * @property TendersWe[] $tendersWe
 * @property TendersResults[] $winner
 */
class Tenders extends \yii\db\ActiveRecord
{
    /**
     * Набор значений для поля "Согласован ли договор"
     */
    const CONTRACT_APPROVED_VALUE_NO = 0;
    const CONTRACT_APPROVED_VALUE_YES = 1;
    const CONTRACT_APPROVED_VALUE_PENDING = 2;

    /**
     * Количество символов, из которых состоит номер закупки для определеняи ссылки
     * Используется в случае идентификации по номеру закупки
     */
    const REG_NUMBER_LENGTH_223 = 11;
    const REG_NUMBER_LENGTH_44 = 19;

    /**
     * Способы поиска тендера
     */
    const FIND_TENDER_TOOL_ID = 0; // по номеру
    const FIND_TENDER_TOOL_REQS = 1; // по реквизитам контрагента и наименованию закупки

    /**
     * Главный URL сайта-источника
     */
    const URL_SRC_MAIN = 'http://zakupki.gov.ru';

    /**
     * Идентификаторы страниц для внутреннего использования
     */
    const TENDERS_223_PAGE_COMMON = 10;
    const TENDERS_223_PAGE_FILES = 11;
    const TENDERS_223_PAGE_LOGS = 12;
    const TENDERS_223_PAGE_PROTOCOLS = 13;

    const TENDERS_44_PAGE_COMMON = 20;
    const TENDERS_44_PAGE_FILES = 21;
    const TENDERS_44_PAGE_LOGS = 22;
    const TENDERS_44_PAGE_RESULTS = 23;

    const TENDERS_504_PAGE_COMMON = 30;
    const TENDERS_504_PAGE_FILES = 31;
    const TENDERS_504_PAGE_LOGS = 32;

    /**
     * Ссылки на страницы, с которых производится сбор информации
     */
    const URL_TENDER_223_COMMON = self::URL_SRC_MAIN . '/223/purchase/public/purchase/info/common-info.html';
    const URL_TENDER_223_FILES = self::URL_SRC_MAIN . '/223/purchase/public/purchase/info/documents.html';
    const URL_TENDER_223_LOGS = self::URL_SRC_MAIN . '/223/purchase/public/purchase/info/journal.html';
    const URL_TENDER_223_PROTOCOLS = self::URL_SRC_MAIN . '/223/purchase/public/purchase/info/protocols.html';

    const URL_TENDER_44_COMMON = self::URL_SRC_MAIN . '/epz/order/notice/ea44/view/common-info.html';
    const URL_TENDER_44_FILES = self::URL_SRC_MAIN . '/epz/order/notice/ea44/view/documents.html';
    const URL_TENDER_44_LOGS = self::URL_SRC_MAIN . '/epz/order/notice/ea44/view/event-journal.html';
    const URL_TENDER_44_RESULTS = self::URL_SRC_MAIN . '/epz/order/notice/ea44/view/supplier-results.html';

    const URL_TENDER_504_COMMON = self::URL_SRC_MAIN . '/epz/order/notice/ok504/view/common-info.html';

    const CHAPTER_NAME_44_FILES_PROTOCOL = 'протоколы работы комиссии, полученные с электронной площадки (эп)';

    const DOM_IDS  = [
        'FORM_ID' => 'frmTender',
        'BUTTON_REJECT_ID' => 'btnReject',
        'BUTTON_SUBMIT_REJECT_ID' => 'btnSubmitReject',
        'REASON_MODE_ID' => 'reason-mode',
        // блок с полем для изменений в договоре
        'BLOCK_CC_ID' => 'block-contract-comments',
    ];

    /**
     * @var integer причина проигрыша (виртуальное поле)
     */
    public $loss_reason_id;

    /**
     * @var string URL на закупку
     */
    public $urlSource;

    /**
     * @var array массив отходов, добавленных при создании тендера пользователем
     */
    public $crudeWaste;

    /**
     * @var array оборудование для утилизации
     */
    public $we;

    /**
     * @var integer инструмент для поиска (0 - позиционирование (поиск по номеру тендера), 1 - поиск по реквизитам и наименованию)
     */
    public $findTool;

    /**
     * Группа полей для поиска по ИНН, КПП и предмету закупки
     * @var string
     */
    public $ftInn;
    public $ftKpp;
    public $ftTitle;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['org_id', 'tp_id', 'manager_id', 'conditions', 'ta_id', 'amount_start', 'is_contract_approved'], 'required'],
            [['created_at', 'created_by', 'tk_id', 'state_id', 'stage_id', 'org_id', 'fo_ca_id', 'tp_id', 'manager_id', 'responsible_id', 'placed_at', 'date_stop', 'date_sumup', 'date_auction', 'ta_id', 'is_notary_required', 'is_contract_edit', 'deferral', 'is_contract_approved', 'complexity', 'lr_id', 'loss_reason_id'], 'integer'],
            [['is_notary_required', 'is_contract_edit'], 'boolean'],
            [['title', 'conditions', 'contract_comments', 'comment'], 'string'],
            [['date_complete', 'urlSource', 'we', 'findTool', 'ftInn', 'ftKpp', 'ftTitle'], 'safe'],
            [['amount_start', 'amount_offer', 'amount_fo', 'amount_fc'], 'number'],
            [['law_no'], 'string', 'max' => 20],
            [['oos_number'], 'string', 'max' => 25],
            [['revision'], 'string', 'max' => 3],
            [['fo_ca_name'], 'string', 'max' => 255],
            [['urlSource'], 'trim'],
            [['law_no', 'oos_number', 'revision', 'title', 'fo_ca_name', 'conditions', 'comment'], 'default', 'value' => null],
            [['amount_offer'], 'default', 'value' => 0],
            [['oos_number'], 'unique'],
            [['tk_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersKinds::class, 'targetAttribute' => ['tk_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['manager_id' => 'id']],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['org_id' => 'id']],
            [['responsible_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['responsible_id' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersStates::class, 'targetAttribute' => ['state_id' => 'id']],
            [['stage_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersStages::class, 'targetAttribute' => ['stage_id' => 'id']],
            [['ta_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersApplications::class, 'targetAttribute' => ['ta_id' => 'id']],
            [['tp_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersPlatforms::class, 'targetAttribute' => ['tp_id' => 'id']],
            [['lr_id'], 'exist', 'skipOnError' => true, 'targetClass' => LicensesRequests::class, 'targetAttribute' => ['lr_id' => 'id']],
            // собственные правила валидации
            ['state_id', 'validateState'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'tk_id' => 'Вид конкурса',
            'law_no' => 'Номер закона, по которому осуществляется закупка',
            'oos_number' => 'Номер закупки',
            'revision' => 'Редакция заявки',
            'state_id' => 'Статус внутренний',
            'stage_id' => 'Этап закупки',
            'title' => 'Наименование закупки',
            'org_id' => 'Организация',
            'fo_ca_id' => 'Контрагент',
            'fo_ca_name' => 'Наименование контрагента из Fresh office',
            'tp_id' => 'Тендерная площадка',
            'we' => 'Используемое оборудование',
            'manager_id' => 'Менеджер',
            'responsible_id' => 'Исполнитель',
            'conditions' => 'Особые требования',
            'placed_at' => 'Дата размещения извещения',
            'date_complete' => 'Срок выполнения работ (услуг)',
            'date_stop' => 'Дата окончания приема заявок',
            'date_sumup' => 'Дата подведения итогов',
            'date_auction' => 'Дата проведения аукциона',
            'ta_id' => 'Форма подачи',
            'is_notary_required' => 'Требуется ли нотариальное заверение документов',
            'is_contract_edit' => 'Возможно ли внесение изменений в текст договора',
            'amount_start' => 'Начальная максимальная цена',
            'amount_offer' => 'Наше ценовое предложение',
            'amount_fo' => 'Сумма обеспечения заявки (order funding)',
            'amount_fc' => 'Сумма обеспечения договора (contract funding)',
            'deferral' => 'Срок оплаты (количество дней отсрочки платежа)',
            'is_contract_approved' => 'Договор согласован', // 0 - нет, 1 - да, 2 - на согласовании
            'complexity' => 'Сложность', // диапазон 1-3
            'lr_id' => 'Запрос лицензий',
            'contract_comments' => 'Изменения в договоре',
            'comment' => 'Комментарий',
            // виртуальные поля
            'urlSource' => 'Ссылка на закупку',
            'crudeWaste' => 'Отходы',
            'ftInn' => 'ИНН',
            'ftKpp' => 'КПП',
            'ftTitle' => 'Наименование закупки',
            'loss_reason_id' => 'Причина проигрыша',
            // вычисляемые поля
            'createdByProfileName' => 'Автор создания',
            'stateName' => 'Статус',
            'orgName' => 'Организация',
            'managerProfileName' => 'Менеджер',
            'responsibleProfileName' => 'Исполнитель',
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
                'preserveNonEmptyValues' => true,
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
                'preserveNonEmptyValues' => true,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                if (Yii::$app->user->can('tenders_manager')) {
                    // согласование не требуется, если создает специалист по тендерам
                    $this->state_id = TendersStates::STATE_СОГЛАСОВАНА;
                }
                else {
                    $this->state_id = TendersStates::STATE_ЧЕРНОВИК;
                }
            }

            // идентификация контрагента
            if (!empty($this->fo_ca_id) && empty($this->fo_ca_name)) {
                $foCompany = foCompany::findOne($this->fo_ca_id);
                if ($foCompany) {
                    $this->fo_ca_name = trim($foCompany->COMPANY_NAME);
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
            // извлекаем файлы к тендеру после его записи в базу
            // по номеру закона можно определить страницу, с которой можно получить ссылки на файлы
            $lawNo = (int)$this->law_no;
            $lawNo++;
            $files = $this->fetchTenderByNumber($lawNo);
            if (isset($files['law_no'])) { unset($files['law_no']); }

            if (is_array($files) && count($files) > 0) {
                $this->obtainAuctionFiles($files);
            }
            unset($files);

            // также извлекаем историю изменений из журнала событий
            $lawNo++;
            $logs = $this->fetchTenderByNumber($lawNo);
            if (isset($logs['law_no'])) { unset($logs['law_no']); }
            if (is_array($logs) && count($logs) > 0) {
                $this->obtainAuctionLogs($logs);
            }
            unset($logs);

            // если завку создает менеджер отдела продаж, то необходимо отправить уведомления на E-mail руководству
            if (Yii::$app->user->can('sales_department_manager')) {
                // sales_department_head
                $letter = Yii::$app->mailer->compose([
                    'html' => 'tenderHasBeenCreated-html',
                ], ['model' => $this])
                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                    ->setSubject('Создана заявка на участие в тендере');

                // отправка писем обязательным получателям
                foreach (ResponsibleForProduction::find()->where(['type' => ResponsibleForProduction::TYPE_NEW_TENDER])->all() as $receiver) {
                    /* @var $receiver ResponsibleForProduction */

                    $email = $letter;
                    $email->setTo($receiver->receiver);
                    try { $email->send(); } catch (\Exception $exception) {}

                    unset($email);
                }
            }
        }
        else {
            if (isset($changedAttributes['state_id'])) {
                // проверим, изменился ли статус тендера
                if ($changedAttributes['state_id'] != $this->state_id) {
                    $stateName = $this->stateName;

                    $tenderStateHasBeenChanged = null;

                    switch ($this->state_id) {
                        case TendersStates::STATE_СОГЛАСОВАНА:
                            // отправим специалистам тендерного отдела соответствующее уведомление
                            $tenderStateHasBeenChanged = [
                                'subject' => 'Участие в тендере согласовано руководством',
                                'view' => 'tenderStateHasBeenChanged',
                                'viewParams' => ['model' => $this, 'mean' => 'Руководством согласована новая закупка для участия: '],
                            ];

                            break;
                        case TendersStates::STATE_ОТКАЗ:
                            // статус изменился на "Отказ", отправим автору тендера соответствующее уведомление
                            $tenderStateHasBeenChanged = [
                                'subject' => 'По тендеру получен отказ',
                                'view' => 'poReject',
                                'viewParams' => ['model' => $this],
                            ];

                            break;
                        case TendersStates::STATE_ПОБЕДА:
                        case TendersStates::STATE_ДОЗАПРОС:
                        case TendersStates::STATE_ПРОИГРЫШ:
                        case TendersStates::STATE_ОТМЕНЕН_ЗАКАЗЧИКОМ:
                        case TendersStates::STATE_ОПОЗДАНИЕ:
                            // статус изменился на один из этих, отправка уведомления автору
                            $tenderStateHasBeenChanged = [
                                'subject' => 'Статус закупки изменился',
                                'view' => 'tenderStateHasBeenChanged',
                                'viewParams' => ['model' => $this, 'mean' => 'Статус закупки изменился на ' . $stateName . ': '],
                            ];

                            if ($this->state_id == TendersStates::STATE_ПРОИГРЫШ) {
                                if (empty(TendersLr::findOne(['tender_id' => $this->id]))) {
                                    (new TendersLr([
                                        'tender_id' => $this->id,
                                        'lr_id' => $this->loss_reason_id,
                                    ]))->save();
                                }
                            }

                            break;
                    }

                    if (!empty($tenderStateHasBeenChanged)) {
                        $receivers[] = 'tender@1nok.ru';

                        $receiver = $this->createdByEmail;
                        if (!empty($receiver)) {
                            $receivers[] = $receiver;
                            unset($receiver);
                            if (Yii::$app->authManager->checkAccess($this->created_by, 'sales_department_head')) {
                                // если автор - менеджер отдела продаж, то письмо необходимо продублировать также его начальнику
                                $heads = User::find()->where([
                                    'and',
                                    ['id' => AuthAssignment::find()->select('user_id')->where(['item_name' => 'sales_department_head'])],
                                    ['is', 'blocked_at', null],
                                ])->all();
                                foreach ($heads as $head) {
                                    $receivers[] = $head->email;
                                }
                            }
                        }

                        $letter = Yii::$app->mailer->compose([
                            'html' => $tenderStateHasBeenChanged['view'] . '-html',
                        ], $tenderStateHasBeenChanged['viewParams'])
                            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                            ->setSubject($tenderStateHasBeenChanged['subject']);

                        foreach ($receivers as $receiver) {
                            $letter->setTo($receiver)->send();
                        }
                    }

                    $oldStateName = '';
                    $oldState = TendersStates::findOne($changedAttributes['state_id']);
                    if ($oldState != null) $oldStateName = ' с ' . $oldState->name;

                    (new TendersLogs([
                        'tender_id' => $this->id,
                        'description' => 'Изменение статуса' . $oldStateName . ' на ' . $stateName,
                    ]))->save();
                }
            }

            // если номер закупки отсутствовал, но появился, выполним первичную идентификацию
            if (
                isset($changedAttributes['oos_number']) &&
                $changedAttributes['oos_number'] != $this->oos_number &&
                !empty($this->oos_number)
            ) {
                $this->primaryRecognition();
            }
        }
    }

    /**
     * Удаление связанных объектов перед удалением текущего.
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // удаляем табличную часть "Отходы"
            TendersTp::deleteAll(['tender_id' => $this->id]);

            // удаляем информацию об используемом оборудовании
            TendersWe::deleteAll(['tender_id' => $this->id]);

            // удаляем информацию из журнала событий
            TendersLogs::deleteAll(['tender_id' => $this->id]);

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = TendersFiles::find()->where(['tender_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function validateState()
    {
        if ($this->state_id == TendersStates::STATE_ОТКАЗ && empty($this->comment)) {
            $this->addError('comment', 'Заполнение причины отказа обязательно.');
        }
    }

    /**
     * Возвращает массив с возможными интерпретациями поля "Номер закона".
     * @return array
     */
    public function fetchLaws()
    {
        return [
            [
                'id' => self::TENDERS_223_PAGE_COMMON,
                'name' => '223-ФЗ',
                'title' => 'О закупках товаров, работ, услуг отдельными видами юридических лиц',
                'issued_at' => '18.07.2011',
            ],
            [
                'id' => self::TENDERS_44_PAGE_COMMON,
                'name' => '44-ФЗ',
                'title' => 'О контрактной системе в сфере закупок товаров, работ, услуг для обеспечения государственных и муниципальных нужд',
                'issued_at' => '05.04.2013',
            ],
            [
                'id' => self::TENDERS_504_PAGE_COMMON,
                'name' => '504-ФЗ',
                'title' => 'О внесении изменений в Федеральный закон № 44',
                'issued_at' => '31.12.2017',
            ],
        ];
    }

    /**
     * Возвращает массив уровней сложности.
     * @return array
     */
    public static function fetchComplexityLevels()
    {
        return [
            [
                'id' => 1,
                'name' => 'I',
            ],
            [
                'id' => 2,
                'name' => 'II',
            ],
            [
                'id' => 3,
                'name' => 'III',
            ],
        ];
    }

    /**
     * Конвертирует дату из человекопонятного вида в число. Заходит как: 16.09.2019 11:21 (МСК+2)
     * @param $src
     * @return integer
     */
    private function convertDate($src)
    {
        $result = trim($src);
        if (!empty($result)) {
            $result = mb_substr($result, 6, 4) . '-' . mb_substr($result, 3, 2) . '-' . mb_substr($result, 0, 2) . ' ' . mb_substr($result, 11, 2) . ':' . mb_substr($result, 14, 2) . ':00';
            if (false != strtotime($result)) {
                return strtotime($result);
            }
        }

        return null;
    }

    /**
     * Возвращает массив с возможными значениями поля "Договор согласован".
     * @return array
     */
    public static function fetchIsContractApprovedValues()
    {
        return [
            [
                'id' => self::CONTRACT_APPROVED_VALUE_NO,
                'name' => 'Нет',
            ],
            [
                'id' => self::CONTRACT_APPROVED_VALUE_YES,
                'name' => 'Да',
            ],
            [
                'id' => self::CONTRACT_APPROVED_VALUE_PENDING,
                'name' => 'На согласовании',
            ],
        ];
    }

    /**
     * Делает выборку значений для поля "Договор согласован" и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfIsContractApprovedForSelect2()
    {
        return ArrayHelper::map(self::fetchIsContractApprovedValues(), 'id', 'name');
    }

    /**
     * Делает выборку значений для поля "Уровень сложности" и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfComplexityForSelect2()
    {
        return ArrayHelper::map(self::fetchComplexityLevels(), 'id', 'name');
    }

    /**
     * Выполняет повторную первичную идентификацию.
     * @throws \yii\base\InvalidConfigException
     */
    public function primaryRecognition()
    {
        if (!empty($this->oos_number)) {
            if (strlen($this->oos_number) == Tenders::REG_NUMBER_LENGTH_223) {
                $mode = Tenders::TENDERS_223_PAGE_COMMON;
            }
            elseif (strlen($this->oos_number) == Tenders::REG_NUMBER_LENGTH_44) {
                $mode = Tenders::TENDERS_44_PAGE_COMMON;
            }
        }

        if (!empty($mode)) {
            $tenderData = $this->fetchTenderByNumber($mode);
            //var_dump($tenderData);

            $this->updateAttributes([
                'law_no' => $tenderData['law_no'],
                'tk_id' => $tenderData['tk_id'],
                'tp_id' => $tenderData['tp_id'],
                'fo_ca_id' => $tenderData['fo_ca_id'],
                'fo_ca_name' => $tenderData['customer_name'],
                'revision' => $tenderData['revision'],
                'title' => $tenderData['name'],
                'amount_start' => $tenderData['price'],
                'amount_fo' => $tenderData['amount_fo'],
                'amount_fc' => $tenderData['amount_fc'],
                'placed_at' => $tenderData['placed_at_u'],
                'date_stop' => $tenderData['pf_date_u'],
                'date_sumup' => $tenderData['su_date_u'],
                'date_auction' => $tenderData['auction_at_u'],
            ]);

            // сделаем запись в журнал событий
            (new TendersLogs([
                'tender_id' => $this->id,
                'description' => 'Выполнена повторная первичная идентификация.',
            ]))->save();
        }
    }

    /**
     * Выкачивает файлы по ссылкам из массива. Если передается параметр $checkIfExists в значении true, то дополнительно
     * выполняется проверка существования файла (невозможно для новых).
     * @param $files array массив файлов, которые удастся обнаружить в момент вызова функции
     * @param bool $checkIfExists
     * @throws \yii\base\Exception
     * @throws \yii\httpclient\Exception
     * @return mixed
     */
    public function obtainAuctionFiles($files, $checkIfExists = false)
    {
        $newFiles = [];
        $existingFiles = [];
        if ($checkIfExists) {
            $existingFiles = $this->tendersSourceIdsFiles;
        }

        $client = new Client();
        $pifp = TendersFiles::getUploadsFilepath($this);

        if (is_array($files) && count($files) > 0) {
            foreach ($files as $file) {
                // скачиваем файлы по очереди в папку для тендеров
                if (isset($file['url'])) {
                    // проверка существования файла
                    if ($checkIfExists && in_array($file['src_id'], $existingFiles)) {
                        // выполняется обновление файлов, обнаружен дубликат, пропускаем такой файл
                        continue;
                    }

                    $response = $client->get($file['url'], null, ['user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36 OPR/58.0.3135.118'])->send();
                    if ($response->isOk) {
                        if ($file['ct_id'] != TendersContentTypes::CONTENT_TYPE_ПРОТОКОЛЫ && preg_match('~filename=(?|"([^"]*)"|\'([^\']*)\'|([^;]*))~', $response->headers['content-disposition'], $match)) {
                            $ofn = urldecode($match[1]);
                        }
                        elseif (!empty($file['ofn'])) {
                            // если не удалось определить имя файла, берем то, которое было в атрибуте title ссылки
                            $ofn = $file['ofn'];
                            if (StringHelper::endsWith($ofn, ' ()')) {
                                $ofn = str_replace(' ()', '', $ofn);
                            }
                        }

                        if (!empty($ofn)) {
                            $fileAttached_fn = strtolower(Yii::$app->security->generateRandomString() . '.' . pathinfo($ofn)['extension']);
                            $fileAttached_ffp = $pifp . '/' . $fileAttached_fn;

                            if (false !== file_put_contents($fileAttached_ffp, $response->content)) {
                                // файл успешно сохранен, сделаем запись в базе данных
                                $model = new TendersFiles([
                                    'tender_id' => $this->id,
                                    'ffp' => $fileAttached_ffp,
                                    'fn' => $fileAttached_fn,
                                    'ofn' => $ofn,
                                    'size' => filesize($fileAttached_ffp),
                                    'revision' => !empty($file['revision']) ? $file['revision'] : null,
                                    'src_id' => $file['src_id'],
                                ]);

                                // дата размещения на площадке
                                if (!empty($file['uploaded_at'])) {
                                    $model->uploaded_at = $file['uploaded_at'];
                                }

                                // тип контента
                                if (!empty($file['ct_id'])) {
                                    // если уже задан тип контента, то разумеется, берем его
                                    $model->ct_id = $file['ct_id'];
                                }

                                if ($model->save()) {
                                    if ($checkIfExists) {
                                        $newFiles[] = [
                                            'ffp' => $fileAttached_ffp,
                                            'fn' => $ofn,
                                        ];
                                    }
                                }
                            }

                            unset($fileAttached_fn);
                            unset($fileAttached_ffp);
                        }
                    }
                }
            }

            // возвращаем массив вновь загруженных файлов (для рассылки заинтересованным лицам)
            if ($checkIfExists && count($newFiles) > 0) {
                return $newFiles;
            }
        }
    }

    /**
     * Извлекает записи журнала событий на странице закупки, помещает эти записи в базу с пометкой "Извне".
     * @param $logs
     */
    public function obtainAuctionLogs($logs)
    {
        ArrayHelper::multisort($logs, 'created_at', SORT_ASC);
        foreach ($logs as $log) {
            if (!empty($log['created_at']) && !empty($log['description'])) {
                (new TendersLogs([
                    'created_at' => $log['created_at'],
                    'tender_id' => $this->id,
                    'type' => TendersLogs::TYPE_ИСТОЧНИК,
                    'description' => $log['description'],
                ]))->save();
            }
        }
    }

    /**
     * Рендерит необходимые кнопки для управления формой тендера.
     * @return mixed
     */
    public function renderSubmitButtons()
    {
        $roleRoot = Yii::$app->user->can('root');
        $roleTenders = Yii::$app->user->can('tenders_manager');
        $roleManager = Yii::$app->user->can('sales_department_manager');
        $roleHeadManager = Yii::$app->user->can('sales_department_head');

        $controlButtons = [
            'index' => Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . TendersController::ROOT_LABEL, TendersController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']),
            'create' => Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']),
            'save' => Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']),
            'approve_request' => Html::submitButton('Отправить на согласование <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'approve_request', 'title' => 'Отправить начальнику отдела продаж на согласование']),
            'approve' => Html::submitButton('Согласовать', ['class' => 'btn btn-success btn-lg', 'name' => 'approve', 'title' => 'Согласовать']),
            'reject' => Html::submitButton('<i class="fa fa-times" aria-hidden="true"></i> Отказать', ['id' => self::DOM_IDS['BUTTON_REJECT_ID'], 'class' => 'btn btn-danger btn-lg', 'name' => 'reject', 'title' => 'Отказать в согласовании (обязательно нужно будет указать причину согласования)']),
            'revoke' => Html::submitButton('<i class="fa fa-repeat" aria-hidden="true"></i> Отозвать', ['class' => 'btn btn-warning btn-lg', 'name' => 'revoke']),
            'take_over' => Html::submitButton('Взять в работу', ['class' => 'btn btn-default btn-lg', 'name' => 'take_over']),
            'submitted' => Html::submitButton('Заявка подана', ['class' => 'btn btn-default btn-lg', 'name' => 'submitted']),
            'refinement' => Html::submitButton('Дозапрос', ['class' => 'btn btn-default btn-lg', 'name' => 'refinement']),
            'victory' => Html::submitButton('<i class="fa fa-trophy text-success" aria-hidden="true"></i> Победа', ['class' => 'btn btn-default btn-lg', 'name' => 'victory']),
            'defeat' => Html::submitButton('Проигрыш', ['class' => 'btn btn-default btn-lg', 'id' => 'btnDefeat', 'name' => 'defeat']),
            'abyss' => Html::submitButton('Без результатов', ['class' => 'btn btn-default btn-lg', 'name' => 'abyss']),
            'recede' => Html::submitButton('<i class="fa fa-window-close" aria-hidden="true"></i> Отказ от участия', ['class' => 'btn btn-default btn-lg', 'name' => 'recede', 'title' => 'Отказаться от участия добровольно']),
            'withdrew' => Html::submitButton('Отменен заказчиком', ['class' => 'btn btn-default btn-lg', 'name' => 'withdrew']),
            'late' => Html::submitButton('Не успели', ['class' => 'btn btn-default btn-lg', 'name' => 'late']),
        ];

        $result = $controlButtons['index'] . ' ';

        if ($this->state_id == TendersStates::STATE_ПОБЕДА) {
            $result .= ' ' . $controlButtons['save'];
        }
        elseif ($this->state_id <= TendersStates::STATE_ДОЗАПРОС && !$this->isNewRecord) {
            $result .= ' ' . $controlButtons['save'];
            if ($roleTenders) {
                $result .= $controlButtons['recede'];
            }
        }

        switch ($this->state_id) {
            case null:
                $result .= $controlButtons['create'];
                break;
            case TendersStates::STATE_ЧЕРНОВИК:
            case TendersStates::STATE_ОТКАЗ:
                if ($roleManager) {
                    $result .= ' ' . $controlButtons['approve_request'];
                }

                break;
            case TendersStates::STATE_СОГЛАСОВАНИЕ_РОП:
                if ($roleHeadManager) {
                    // начальник отдела продаж может согласовать или отказать в согласовании
                    $result .= ' ' . $controlButtons['approve'] . ' ' . $controlButtons['reject'];
                }

                if ($this->created_by == Yii::$app->user->id) {
                    // автор может отозвать свою закупку в черновики
                    $result .= ' ' . $controlButtons['revoke'];
                }

                break;
            case TendersStates::STATE_СОГЛАСОВАНИЕ_РУКОВОДСТВОМ:
                if ($roleRoot) {
                    // руководитель может согласовать или отказать
                    $result .= ' ' . $controlButtons['approve'] . ' ' . $controlButtons['reject'];
                }

                if ($this->created_by == Yii::$app->user->id) {
                    // автор может отозвать свою закупку в черновики
                    $result .= ' ' . $controlButtons['revoke'];
                }

                break;
            case TendersStates::STATE_СОГЛАСОВАНА:
                if ($roleTenders && empty($this->responsible_id)) {
                    // специалист по тендерам может взять в работу
                    // если, правда, никто другой пока еще не взял
                    $result .= ' ' . $controlButtons['take_over'];
                }

                if ($roleRoot) {
                    // руководитель может отозвать свое согласие
                    $result .= ' ' . $controlButtons['revoke'];
                }

                break;
            case TendersStates::STATE_В_РАБОТЕ:
                if ($roleTenders) {
                    // специалист по тендерам может отметить, что заявка подана
                    $result .= ' ' . $controlButtons['submitted'];
                }

                break;
            case TendersStates::STATE_ЗАЯВКА_ПОДАНА:
            case TendersStates::STATE_ДОЗАПРОС:
                if ($roleTenders) {
                    if ($this->law_no != self::TENDERS_44_PAGE_COMMON && $this->state_id <> TendersStates::STATE_ДОЗАПРОС) {
                        // специалист по тендерам может отметить, что требуется уточнение данных
                        // стоит отметить, что по 44-му закону такой кнопки быть не может
                        $result .= ' ' . $controlButtons['refinement'];
                    }
                    $result .= ' ' . $controlButtons['victory'] . ' ' . $controlButtons['defeat'] . ' ' . $controlButtons['abyss'];
                }

                if ($roleRoot) {
                    $result .= ' ' . $controlButtons['defeat'];
                }
                break;
            case TendersStates::STATE_ПРОИГРЫШ:
                $result .= ' ' . $controlButtons['save'];

                break;
        }

        if ($this->state_id >= TendersStates::STATE_СОГЛАСОВАНА && !$this->isNewRecord) {
            if ($this->state_id != TendersStates::STATE_ОТМЕНЕН_ЗАКАЗЧИКОМ) {
                $result .= ' ' . $controlButtons['withdrew'];
            }
            if ($this->state_id != TendersStates::STATE_ОПОЗДАНИЕ) {
                $result .= ' ' . $controlButtons['late'];
            }
        }

        return $result;
    }

    /**
     * Выполняет журналирование изменений общей информации. Проводит анализ изменений, вносит изменения при выявлении
     * расхождений, делает запись в таблицу истории изменений.
     * @param array $data массив обновленной информации
     */
    public function logChangesCommon($data)
    {
        if (is_array($data)) {
            foreach ($data as $field) {
                if ($field['newValue'] != $this->{$field['attributeName']}) {
                    if ($this->updateAttributes([
                            $field['attributeName'] => $field['newValue'],
                        ]) > 0 ) {
                        // значение изменлось, обновлено в базе успешно, сделаем запись в журнал об этом
                        if (isset($field['oldFormatted'])) {
                            // если передается уже форматированное старое значение, то в журнал пишем именно его
                            $oldFormatted = $field['oldFormatted'];
                        }
                        else {
                            $oldFormatted = $this->{$field['attributeName']};
                        }

                        if (isset($field['newFormatted'])) {
                            // если передается уже форматированное новое значение, то в журнал пишем именно его
                            $newFormatted = $field['newFormatted'];
                        }
                        else {
                            $newFormatted = $field['newValue'];
                        }

                        (new TendersLogs([
                            'tender_id' => $this->id,
                            'type' => TendersLogs::TYPE_ИСТОЧНИК,
                            'description' => 'Значение поля "' . $this->getAttributeLabel($field['attributeName']) . '" было изменено' . (!empty($oldFormatted) ? ' с ' . $oldFormatted : '') . ' на "' . (!empty($newFormatted) ? $newFormatted : '[пустое]') . '".',
                        ]))->save();
                    };
                }
            }
        }
    }

    /**
     * Значение на входе превращает в дату в строго определенном формате.
     * @param $mode integer режим работы, для разных представлений даты разные
     * @param $value
     * @return string
     */
    private function formDateFromString($mode, $value)
    {
        $result = '';
        switch ($mode) {
            case 1:
                // 11.09.2019 (МСК)
                $result = mb_substr($value, 6, 4) . '-' . mb_substr($value, 3, 2) . '-' . mb_substr($value, 0, 2) . ' 04:00:00';
                break;
            case 2:
                // 08.11.2019
                $result = mb_substr($value, 6, 4) . '-' . mb_substr($value, 3, 2) . '-' . mb_substr($value, 0, 2);
                break;
            case 3:
                // 05.09.2019 в 14:00 (МСК)
                $result = mb_substr($value, 6, 4) . '-' . mb_substr($value, 3, 2) . '-' . mb_substr($value, 0, 2) . ' ' . mb_substr($value, 13, 2) . ':' . mb_substr($value, 16, 2) . ':00';
                break;
            case 4:
                // 24.09.2019 10:00
                $result = mb_substr($value, 6, 4) . '-' . mb_substr($value, 3, 2) . '-' . mb_substr($value, 0, 2) . ' ' . mb_substr($value, 11, 2) . ':' . mb_substr($value, 14, 2) . ':00';
                break;
        }

        return $result;
    }

    /**
     * @param $page integer разновидность верстки
     * @param $alreadyCollectedInfo array результирующий массив с уже собранной информацией о тендере
     * @param $chapterName string заголовок блока
     * @param $fieldName string название поля
     * @param $fieldValue string значение поля
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function fillFieldValue($page, $alreadyCollectedInfo, $chapterName, $fieldName, $fieldValue)
    {
        $result = [];

        switch ($chapterName) {
            case 'сведения о закупке':
            case 'общие сведения о закупке':
            case 'общая информация о закупке':
                switch ($fieldName) {
                    case 'реестровый номер извещения':
                        $result['regNumber'] = $fieldValue;
                        break;
                    case 'способ размещения закупки':
                    case 'способ определения поставщика (подрядчика, исполнителя)':
                        $result['placeMethod'] = $fieldValue;
                        break;
                    /*
                    case 'размещение осуществляет':
                        $customer_name = Html::decode(trim($td->nextSibling()->plaintext));
                        $customer_name = preg_replace('| +|', ' ', $customer_name); // множество пробелов заменяем одним
                        $customer_name = str_replace("\r\n", '', $customer_name);
                        if (StringHelper::startsWith($customer_name, 'Заказчик')) {
                            $customer_name = str_replace('Заказчик ', '', $customer_name);
                        }
                        $result['customer_name'] = $customer_name;

                        break;
                    */
                    case 'наименование закупки':
                    case 'наименование объекта закупки':
                        $result['name'] = $fieldValue;
                        break;
                    case 'этап закупки':
                        $result['stage'] = $fieldValue;
                        break;
                    case 'наименование электронной площадки в информационно-телекоммуникационной сети интернет':
                        $result['tp_name'] = $fieldValue;
                        break;
                    case 'адрес электронной площадки в информационно-телекоммуникационной сети интернет':
                        $result['tp_url'] = $fieldValue;
                        break;
                    case 'редакция':
                        $result['revision'] = $fieldValue;
                        break;
                    case 'дата размещения извещения':
                        $date = $this->formDateFromString(1, $fieldValue);
                        $result['placed_at_f'] = Yii::$app->formatter->asDate($date, 'php:d.m.Y');
                        $result['placed_at_u'] = strtotime($date);
                        unset($date);

                        break;
                }
                break;
            case 'заказчик':
                switch ($fieldName) {
                    case 'наименование организации':
                        $result['customer_name'] = Html::decode($fieldValue);
                        break;
                    case 'инн':
                        $result['customer_inn'] = $fieldValue;
                        break;
                    case 'кпп':
                        $result['customer_kpp'] = $fieldValue;
                        break;
                    case 'огрн':
                        $result['customer_ogrn'] = $fieldValue;
                        break;
                    case 'место нахождения':
                        $result['customer_address_f'] = $fieldValue;
                        break;
                    case 'почтовый адрес':
                        $result['customer_address_m'] = $fieldValue;
                        break;
                }

                break;
            case 'информация об организации, осуществляющей определение поставщика (подрядчика, исполнителя)':
                switch ($fieldName) {
                    case 'организация, осуществляющая размещение':
                        $customer_name = '';
                        if ($fieldValue === '') {
                            // пустой комментарий присутствует в разметке, учтем это
                            // это надо как-то поймать и исправить:
                            //$customer_name = Html::decode(trim($td->nextSibling()->nextSibling()->plaintext));
                        }
                        else {
                            $customer_name = Html::decode($fieldValue);
                        }
                        $result['customer_name'] = $customer_name;
                        //break 3; // вообще уходим отсюда, потому что из этого блока нам нужно только наименование заказчика
                }

                break;
            case 'порядок проведения процедуры':
            case 'информация о процедуре закупки':
            case 'информация о процедуре электронного аукциона':
                switch ($fieldName) {
                    case 'дата и время окончания подачи заявок (по местному времени заказчика)':
                    case 'дата и время окончания подачи заявок':
                    case 'дата и время окончания срока подачи заявок на участие в электронном аукционе':
                        // 223: 05.09.2019 в 14:00 (МСК)
                        // 44: 24.09.2019 10:00
                        if ($page == self::TENDERS_223_PAGE_COMMON) {
                            $date = $this->formDateFromString(3, $fieldValue);
                        }
                        else {
                            $date = $this->formDateFromString(4, $fieldValue);
                        }
                        if (!empty($date)) {
                            $result['pf_date_f'] = Yii::$app->formatter->asDate($date, 'php:d.m.Y H:i');
                            $result['pf_date_u'] = strtotime($date);
                        }

                        // еще вариант, менее надежный, потому что необходимо наверняка знать часовой пояс
                        //$date = \DateTime::createFromFormat('d.m.Y в H:i (МСК)', $date)->format('Y-m-d H:i');
                        unset($date);

                        break;
                    case 'дата подведения итогов':
                        $date = $this->formDateFromString(1, $fieldValue);
                        $result['su_date_f'] = Yii::$app->formatter->asDate($date, 'php:d.m.Y');
                        $result['su_date_u'] = strtotime($date);
                        unset($date);

                        break;
                    case 'дата и время начала подачи заявок':
                        $date = $this->formDateFromString(1, $fieldValue);
                        $result['placed_at_f'] = Yii::$app->formatter->asDate($date, 'php:d.m.Y');
                        $result['placed_at_u'] = strtotime($date);
                        unset($date);

                        break;
                    case 'дата проведения аукциона в электронной форме':
                        $date = $this->formDateFromString(2, $fieldValue);
                        $result['auction_at_f'] = Yii::$app->formatter->asDate($date, 'php:d.m.Y');
                        $result['auction_at_u'] = strtotime($date);
                        unset($date);

                        break;
                    case 'время проведения аукциона':
                        $result['auction_at_f'] = Yii::$app->formatter->asDate($alreadyCollectedInfo['auction_at_f'] . ' ' . $fieldValue, 'php:d.m.Y H:i');
                        $result['auction_at_u'] = strtotime($result['auction_at_f']);
                        break;
                }

                break;
            case 'начальная (максимальная) цена контракта':
                switch ($fieldName) {
                    case 'начальная (максимальная) цена контракта':
                        $price = $fieldValue;
                        $price = str_replace([' ', chr(0xC2) . chr(0xA0)], '', $price);
                        $price = str_replace(',', '.', $price);
                        $result['price'] = $price;
                        break;
                }

                break;
            case 'обеспечение заявки':
            case 'обеспечение исполнения контракта':
                $resultFieldName = 'amount_fc';
                switch ($fieldName) {
                    case 'размер обеспечения заявки':
                        $resultFieldName = 'amount_fo';
                    case 'размер обеспечения исполнения контракта':
                        $amount = $fieldValue;
                        $amount = str_replace('Российский рубль', '', $amount);
                        $amount = str_replace([' ', chr(0xC2) . chr(0xA0)], '', $amount);
                        if (false !== strpos($amount, '%') && !empty($result['price'])) {
                            $amount = $amount * $result['price'] / 100;
                        }
                        else {
                            $amount = floatval(str_replace(',', '.', $amount));
                        }

                        $result[$resultFieldName] = $amount;
                        unset($amount);

                        break;
                }
                unset($resultFieldName);

                break;
        }

        return $result;
    }

    /**
     * Выполняет переход на страницу тендера и парсит ее, извлекая необходимую информацию.
     * @param $page integer страница, с которой необходимо извлечь данные
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function fetchTenderByNumber($page = self::TENDERS_223_PAGE_COMMON)
    {
        $url = '';
        switch ($page) {
            case self::TENDERS_223_PAGE_COMMON:
                $url = self::URL_TENDER_223_COMMON;
                break;
            case self::TENDERS_223_PAGE_FILES:
                $url = self::URL_TENDER_223_FILES;
                break;
            case self::TENDERS_223_PAGE_LOGS:
                $url = self::URL_TENDER_223_LOGS;
                break;
            case self::TENDERS_223_PAGE_PROTOCOLS:
                $url = self::URL_TENDER_223_PROTOCOLS;
                break;
            case self::TENDERS_44_PAGE_COMMON:
                $url = self::URL_TENDER_44_COMMON;
                break;
            case self::TENDERS_44_PAGE_FILES:
                $url = self::URL_TENDER_44_FILES;
                break;
            case self::TENDERS_44_PAGE_LOGS:
                $url = self::URL_TENDER_44_LOGS;
                break;
            case self::TENDERS_44_PAGE_RESULTS:
                // запрашиваются результаты конкурса, установим URL страницы, с которой это можно спарсить
                $url = self::URL_TENDER_44_RESULTS;
                break;
            case self::TENDERS_504_PAGE_COMMON:
                $url = self::URL_TENDER_504_COMMON;
                break;
        }

        if (!empty($this->oos_number) && !empty($url)) {
            $result = [
                'law_no' => $page,
            ];
            if (in_array($page, [self::TENDERS_44_PAGE_COMMON, self::TENDERS_504_PAGE_COMMON])) {
                $result['regNumber'] = $this->oos_number;
            }

            $client = new Client([
                'responseConfig' => [
                    'format' => Client::FORMAT_URLENCODED,
                ],
            ]);
            $response = $client->createRequest()
                ->setUrl($url)
                ->setMethod('GET')
                ->addHeaders(['user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36 OPR/58.0.3135.118'])
                ->setData(['regNumber' => $this->oos_number])
                ->send();
            if ($response->isOk) {
                try {
                    //$html = \keltstr\simplehtmldom\SimpleHTMLDom::str_get_html($response->content);
                    $html = SimpleHTMLDom::str_get_html($response->content);
                    if ($html->innertext != '') {
                        switch ($page) {
                            case self::TENDERS_223_PAGE_LOGS:
                            case self::TENDERS_44_PAGE_LOGS:
                            case self::TENDERS_504_PAGE_LOGS:
                                ////////////////////////////////////////////////////////////////////////////////////////
                                /// СБОР СО СТРАНИЦЫ "ЖУРНАЛ СОБЫТИЙ"                                                ///
                                ////////////////////////////////////////////////////////////////////////////////////////
                                if ($page == self::TENDERS_223_PAGE_LOGS) {
                                    $logRows = $html->find('table#documentAction>tbody>tr');
                                }
                                else {
                                    $logRows = $html->find('.tabBoxWrapper>table>tbody>tr');
                                }
                                if (count($logRows) > 0) {
                                    foreach ($logRows as $row) {
                                        $pizDate = trim($row->children(0)->plaintext);
                                        try {
                                            //$date = $this->formDateFromString(4, $pizDate);
                                            $pizDate = mb_substr($pizDate, 6, 4) . '-' . mb_substr($pizDate, 3, 2) . '-' . mb_substr($pizDate, 0, 2) . ' ' . mb_substr($pizDate, 11, 2) . ':' . mb_substr($pizDate, 14, 2) . ':00';
                                            $pizDate = strtotime($pizDate);
                                            $description = Html::decode(trim($row->children(1)->plaintext));
                                            if (false != $pizDate && !empty($description)) {
                                                $result[] = [
                                                    'created_at' => $pizDate,
                                                    'description' => $description,
                                                ];
                                            }
                                            unset($pizDate);
                                            unset($description);
                                        }
                                        catch (\Exception $exception) {
                                            if (!empty($this->id)) {
                                                // не удалось че-то, возможно, преобразовать дату
                                                // сделаем запись в журнал внутренних событий
                                                (new TendersLogs([
                                                    'tender_id' => $this->id,
                                                    'description' => 'Не удалось сделать запись в журнал истории событий источника.' . chr(13) . $exception->getMessage(),
                                                ]))->save();
                                            }
                                        }
                                    }
                                }
                                unset($logRows);

                                break;
                            default:
                                $dom = 'h2';
                                $chapterTitles = $html->find($dom);
                                if (count($chapterTitles) > 0) {
                                    // перебираем результат выборки DOM
                                    foreach ($chapterTitles as $chapterTitle) {
                                        $properties = null;

                                        $chapterText = trim(mb_strtolower($chapterTitle->plaintext));
                                        switch ($page) {
                                            case self::TENDERS_223_PAGE_COMMON:
                                            case self::TENDERS_44_PAGE_COMMON:
                                            case self::TENDERS_504_PAGE_COMMON:
                                                ////////////////////////////////////////////////////////////////////////
                                                /// СБОР СО СТРАНИЦЫ "ОБЩАЯ ИНФОРМАЦИЯ"                              ///
                                                ////////////////////////////////////////////////////////////////////////

                                                // <h2 class="blockInfo__title">Общая информация о закупке</h2>
                                                // <section class="blockInfo__section section">
                                                //      <span class="section__title">Наименование электронной площадки в информационно-телекоммуникационной сети "Интернет"</span>
                                                //      <span class="section__info">ЗАО «Сбербанк-АСТ»</span>
                                                // </section>
                                                // nextSibling() для h2 вернет <section class="blockInfo__section section">
                                                // children(0) вернет <span class="section__title">Способ определения поставщика (подрядчика, исполнителя)</span>

                                                /**
                                                 * Было так:
                                                 * nextSibling() для h2 вернет <div class="noticeTabBoxWrapper">
                                                 * children(0) вернет <table>
                                                 * children(0) вернет <tbody>
                                                 */

                                                if ($page == self::TENDERS_223_PAGE_COMMON) {
                                                    $properties = SimpleHTMLDom::str_get_html($chapterTitle->nextSibling()->outertext);
                                                    foreach ($properties->find('div.noticeTabBoxWrapper>table>tbody>tr') as $tr) {
                                                        // перебираем ячейки в строке таблицы
                                                        $fieldName = trim(mb_strtolower($tr->children(0)->plaintext));
                                                        $fieldName = preg_replace('| +|', ' ', $fieldName); // множество пробелов заменяем одним
                                                        $fieldName = str_replace('«', '', $fieldName);
                                                        $fieldName = str_replace('»', '', $fieldName);
                                                        $fieldName = str_replace('"', '', $fieldName);
                                                        $fieldName = str_replace(' </br>', '', $fieldName);

                                                        if (!empty($tr->children(1))) {
                                                            $result = ArrayHelper::merge($result, $this->fillFieldValue($page, $result, $chapterText, $fieldName, trim($tr->children(1)->plaintext)));
                                                        }
                                                    }
                                                }
                                                else {
                                                    $properties = SimpleHTMLDom::str_get_html($chapterTitle->parent()->outertext);
                                                    foreach ($properties->find('.blockInfo__section') as $blockInfo) {
                                                        $fieldName = '';
                                                        if (!empty($blockInfo->children(0))) {
                                                            $fieldName = trim(mb_strtolower($blockInfo->children(0)->plaintext));
                                                            $fieldName = str_replace('"', '', $fieldName);
                                                        }

                                                        if ($blockInfo->children(1)) {
                                                            // бывает, что нет следующего элемента, содержащего значение поля
                                                            // например, Требуется обеспечение исполнения контракта
                                                            $date = trim($blockInfo->children(1)->plaintext);
                                                            if (!empty($date) && $date != 'Время аукциона не определено') {
                                                                $result = ArrayHelper::merge($result, $this->fillFieldValue($page, $result, $chapterText, $fieldName, $date));
                                                            }
                                                        }
                                                    }
                                                }

                                                break;
                                            case self::TENDERS_223_PAGE_FILES:
                                            case self::TENDERS_44_PAGE_FILES:
                                                ////////////////////////////////////////////////////////////////////////////////
                                                /// СБОР СО СТРАНИЦЫ "ДОКУМЕНТЫ ИЗВЕЩЕНИЯ"                                   ///
                                                ////////////////////////////////////////////////////////////////////////////////
                                                switch ($chapterText) {
                                                    case 'документация по закупке':
                                                    case 'протоколы работы комиссии':
                                                    case 'разъяснения документации':
                                                        $block = $chapterTitle->nextSibling()->children(0)->children(0);
                                                        // перебираем строки с ссылками на файлы
                                                        if (count($block->children()) > 1) {
                                                            foreach ($block->children(1)->children() as $tr) {
                                                                $url = null;
                                                                if (isset($tr->children(1)->children(0)->attr['href'])) {
                                                                    $url = $tr->children(1)->children(0)->attr['href'];
                                                                }

                                                                $src_id = null;
                                                                if (preg_match("/.*\?id=(\d+)$/", $url, $output_array)){
                                                                    $src_id = $output_array[1];
                                                                    unset($output_array);
                                                                }

                                                                $ct_id = null;
                                                                switch ($chapterText) {
                                                                    case 'документация по закупке':
                                                                        $ct_id = TendersContentTypes::CONTENT_TYPE_ДОКУМЕНТАЦИЯ; break;
                                                                    case 'протоколы работы комиссии':
                                                                        $ct_id = TendersContentTypes::CONTENT_TYPE_ПРОТОКОЛЫ; break;
                                                                    case 'разъяснения документации':
                                                                        $ct_id = TendersContentTypes::CONTENT_TYPE_РАЗЪЯСНЕНИЯ_ДОКУМЕНТАЦИИ; break;
                                                                }

                                                                $result[] = [
                                                                    'url' => Tenders::URL_SRC_MAIN . $url,
                                                                    'revision' => preg_replace("/[^0-9]/", '', $tr->children(1)->nextSibling()->plaintext),
                                                                    'src_id' => $src_id,
                                                                    'uploaded_at' => $this->convertDate(trim($tr->children(3)->plaintext)), // дата размещения на площадке
                                                                    'ct_id' => $ct_id,
                                                                ];
                                                            }
                                                        }

                                                        break;
                                                    case 'документация, изменение документации':
                                                    case 'разъяснение положений документации об электронном аукционе':
                                                    case self::CHAPTER_NAME_44_FILES_PROTOCOL:
                                                        // parent() - это .centered-header, nextSibling() - это .clear-2, $block->children() - это .row.notice-documents

                                                        $block = $chapterTitle->parent();
                                                        $rows = $block->find('div.row.no-gutters.blockInfo__section');
                                                        if (!empty($rows)) {
                                                            foreach ($rows as $row) {
                                                                try {
                                                                    if (
                                                                        // col-sm-6 b-r => row no-gutters => col-sm-10 => col => row => col-sm
                                                                        (
                                                                            (count($row->children(0)->children(0)->children()) > 1 && $chapterText != self::CHAPTER_NAME_44_FILES_PROTOCOL) ||
                                                                            (count($row->children(0)->children(0)->children(0)->children(0)->children()) > 1 && $chapterText == self::CHAPTER_NAME_44_FILES_PROTOCOL)
                                                                        ) &&
                                                                        // col-sm-6 => blockFilesTabDocs
                                                                        !empty($row->children(1)->children(0))
                                                                    ) {
                                                                        if ($chapterText == self::CHAPTER_NAME_44_FILES_PROTOCOL) {
                                                                            $uploaded_at = strtotime($this->formDateFromString(4, trim($row->children(0)->children(0)->children(0)->children(0)->children(1)->children(0)->children(1)->plaintext)));
                                                                        }
                                                                        else {
                                                                            $uploaded_at = strtotime($this->formDateFromString(4, trim($row->children(0)->children(0)->children(1)->children(0)->children(1)->children(0)->children(1)->plaintext)));
                                                                        }

                                                                        if (false != $uploaded_at) {
                                                                            $attachments = $row->children(1)->children(0)->find('div.attachment.row');
                                                                            if (count($attachments) > 0) {
                                                                                foreach ($attachments as $container) {
                                                                                    if ($chapterText == self::CHAPTER_NAME_44_FILES_PROTOCOL) {
                                                                                        $hasLink = !empty($container->children(0)->children(1));
                                                                                        $link = $container->children(0)->children(1)->children(0);
                                                                                    }
                                                                                    else {
                                                                                        $hasLink = !empty($container->children(0)->children(2));
                                                                                        $link = $container->children(0)->children(2)->children(0);
                                                                                    }
                                                                                    if (!empty($container->children(0)) && $hasLink) {
                                                                                        // перебираем строки с ссылками на файлы
                                                                                        if (!empty($link->attr['class'])) continue; // некоторые ссылки относятся к уже недействущим редакциям

                                                                                        $url = $link->attr['href'];

                                                                                        $src_id = null;
                                                                                        if (preg_match("/.*\?uid=(\w+)$/", $url, $output_array)){
                                                                                            $src_id = $output_array[1];
                                                                                            unset($output_array);
                                                                                        }
                                                                                        else {
                                                                                            $urlParsed = parse_url($url);
                                                                                            if (isset($urlParsed['query'])) {
                                                                                                parse_str($urlParsed['query'], $output_array);
                                                                                                if (null !== $output_array['fid']) {
                                                                                                    $src_id = $output_array['fid'];
                                                                                                }
                                                                                            }
                                                                                            elseif ($chapterText == self::CHAPTER_NAME_44_FILES_PROTOCOL) {
                                                                                                // для протоколов идентификатор может извлекатсья необычным способом
                                                                                                if (preg_match("/.*\/id\/(\d+)\/extract.*$/", $url, $output_array)){
                                                                                                    $src_id = $output_array[1];
                                                                                                }
                                                                                            }
                                                                                        }

                                                                                        $ct_id = null;
                                                                                        switch ($chapterText) {
                                                                                            case 'документация, изменение документации':
                                                                                                $ct_id = TendersContentTypes::CONTENT_TYPE_ДОКУМЕНТАЦИЯ; break;
                                                                                            case 'разъяснение положений документации об электронном аукционе':
                                                                                                $ct_id = TendersContentTypes::CONTENT_TYPE_РАЗЪЯСНЕНИЯ_ДОКУМЕНТАЦИИ; break;
                                                                                            case self::CHAPTER_NAME_44_FILES_PROTOCOL:
                                                                                                $ct_id = TendersContentTypes::CONTENT_TYPE_ПРОТОКОЛЫ; break;
                                                                                        }

                                                                                        $ofn = null;
                                                                                        if (!empty($link->attr['title'])) {
                                                                                            $ofn = $link->attr['title'];
                                                                                        }

                                                                                        $result[] = [
                                                                                            'url' => $url,
                                                                                            'src_id' => $src_id,
                                                                                            'uploaded_at' => $uploaded_at, // дата размещения на площадке, одна на все файлы данной строки
                                                                                            'ct_id' => $ct_id,
                                                                                            'ofn' => $ofn,
                                                                                        ];

                                                                                        unset($ofn, $link);
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                catch (\Exception $exception) {}
                                                            }
                                                        }

                                                        break;
                                                }

                                                break;
                                            case self::TENDERS_223_PAGE_PROTOCOLS:
                                                ////////////////////////////////////////////////////////////////////////
                                                /// СБОР СО СТРАНИЦЫ "ПРОТОКОЛЫ"                                     ///
                                                ////////////////////////////////////////////////////////////////////////
                                                switch ($chapterText) {
                                                    case 'протоколы подэтапа протокол подведения итогов открытого аукциона в электронной форме':
                                                        $block = $chapterTitle->nextSibling()->children(1)->children(0)->children(1);
                                                        // перебираем строки с ссылками на файлы
                                                        if (count($block->children()) > 0) {
                                                            foreach ($block->children() as $tr) {
                                                                $url = null;
                                                                if (isset($tr->children(1)->children(0)->children(0)->children(0)->attr['onclick'])) {
                                                                    $url = $tr->children(1)->children(0)->children(0)->children(0)->attr['onclick'];
                                                                    $url = str_replace('window.open(\'', '', $url);
                                                                    $url = str_replace('\'); return false;', '', $url);
                                                                }

                                                                $src_id = null;
                                                                if (preg_match("/.*\?protocolInfoId=(\d+).*$/", $url, $output_array)){
                                                                    $src_id = $output_array[1];
                                                                    unset($output_array);
                                                                }

                                                                $result[] = [
                                                                    'url' => Tenders::URL_SRC_MAIN . $url,
                                                                    'revision' => preg_replace("/[^0-9]/", '', trim($tr->children(3)->plaintext)),
                                                                    'src_id' => $src_id,
                                                                    'uploaded_at' => $this->convertDate(trim($tr->children(2)->plaintext)), // дата размещения на площадке
                                                                    'ct_id' => TendersContentTypes::CONTENT_TYPE_ПРОТОКОЛЫ,
                                                                ];
                                                            }
                                                        }

                                                        break;
                                                }

                                                break;
                                            case self::TENDERS_44_PAGE_RESULTS:
                                                ////////////////////////////////////////////////////////////////////////////////
                                                /// СБОР СО СТРАНИЦЫ "РЕЗУЛЬТАТЫ ОПРЕДЕЛЕНИЯ ПОСТАВЩИКА"                     ///
                                                ////////////////////////////////////////////////////////////////////////////////
                                                switch ($chapterText) {
                                                    case 'сведения о контракте из реестра контрактов':
                                                        if (!empty($chapterTitle->nextSibling())) {
                                                            $table = $chapterTitle->nextSibling()->children(0);
                                                            if ($table->tag == 'table') {
                                                                // если поставщик определен, в этом месте будет таблица, а иначе тег section
                                                                $tr = $table->children(1)->children(0);

                                                                // в третьей ячейке хранится наименование поставщика
                                                                if (!empty($tr->children(2))) {
                                                                    $result['name'] = trim($tr->children(2)->plaintext);
                                                                }

                                                                // в четвертой ячейке хранится ценовое предложение поставщика
                                                                if (!empty($tr->children(3))) {
                                                                    $price = trim($tr->children(3)->plaintext);
                                                                    $price = preg_replace("/[^,.0-9]/", '', $price);
                                                                    $price = str_replace([' ', chr(0xC2) . chr(0xA0)], '', $price);
                                                                    $price = str_replace(',', '.', $price);
                                                                    $result['price'] = $price;
                                                                    unset($price);
                                                                }

                                                                // в пятой ячейке хранится время принятия решения (размещения документа)
                                                                if (!empty($tr->children(4))) {
                                                                    $result['placed_at'] = $this->convertDate(trim($tr->children(4)->plaintext));
                                                                }
                                                            }
                                                        }

                                                        break;
                                                }
                                        }
                                    }
                                }
                                break;
                        }

                        // попробуем идентифицировать контрагента
                        if (!empty($result['customer_inn'])) {
                            $company_id = foCompanyDetails::find()->select('ID_COMPANY')->where(['INN' => $result['customer_inn'], 'KPP' => $result['customer_kpp']])->scalar();
                            if (!empty($company_id)) $result['fo_ca_id'] = $company_id;
                        }

                        // попробуем идентифицировать торговую площадку
                        if (!empty($result['tp_url'])) {
                            $tpUrl = str_replace('www.', '', $result['tp_url']);
                            $tpUrl = str_replace('http://', '', $tpUrl);
                            $tp = TendersPlatforms::find()->where(['like', 'href', $tpUrl])->one();
                            if ($tp) {
                                $result['tp_id'] = $tp->id;
                                $result['tpName'] = $tp->name;
                            }
                        }

                        // попытка идентифицировать способ размещения
                        if (!empty($result['placeMethod'])) {
                            $tk = TendersKinds::find()->where(['like', 'keywords', $result['placeMethod']])->one();
                            if ($tk) $result['tk_id'] = $tk->id;
                        }
                    }
                }
                catch (\Exception $exception) {
                    echo '<p>Не удалось выполнить запрос!</p>' . $exception;
                }

                return $result;
            }
            else return false;
        }

        return false;
    }

    /**
     * Возвращает наименование закона, по которому проводится тендер.
     * @return string
     */
    public function getLawName()
    {
        $sourceTable = $this->fetchLaws();
        $key = array_search($this->law_no, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTk()
    {
        return $this->hasOne(TendersKinds::class, ['id' => 'tk_id']);
    }

    /**
     * Возвращает наименование разновидности конкурса.
     * @return string
     */
    public function getTkName()
    {
        return !empty($this->tk) ? $this->tk->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Возвращает E-mail автор тендера.
     * @return string
     */
    public function getCreatedByEmail()
    {
        return !empty($this->createdBy) ? $this->createdBy->email : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByRole()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'created_by'])->from(['createdByProfile' => 'profile']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return !empty($this->createdByProfile) ? (!empty($this->createdByProfile->name) ? $this->createdByProfile->name : $this->createdBy->username) : '';
    }

    /**
     * Возвращает E-mail автор тендера.
     * @return string
     */
    public function getCreatedByProfileEmail()
    {
        return !empty($this->createdByProfile) ? (!empty($this->createdByProfile->public_email) ? $this->createdByProfile->public_email : '') : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(TendersStates::class, ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса.
     * @return string
     */
    public function getStateName()
    {
        return !empty($this->state) ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStage()
    {
        return $this->hasOne(TendersStages::class, ['id' => 'stage_id']);
    }

    /**
     * Возвращает наименование этапа закупки.
     * @return string
     */
    public function getStageName()
    {
        return !empty($this->stage) ? $this->stage->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrg()
    {
        return $this->hasOne(Organizations::class, ['id' => 'org_id']);
    }

    /**
     * Возвращает наименование организации.
     * @return string
     */
    public function getOrgName()
    {
        return !empty($this->org) ? $this->org->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTp()
    {
        return $this->hasOne(TendersPlatforms::class, ['id' => 'tp_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
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
    public function getManagerProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'manager_id'])->from(['managerProfile' => 'profile']);
    }

    /**
     * Возвращает имя ответственного менеджера.
     * @return string
     */
    public function getManagerProfileName()
    {
        return !empty($this->managerProfile) ? (!empty($this->managerProfile->name) ? $this->managerProfile->name : $this->manager->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResponsible()
    {
        return $this->hasOne(User::class, ['id' => 'responsible_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResponsibleProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'responsible_id'])->from(['responsibleProfile' => 'profile']);
    }

    /**
     * Возвращает имя исполнителя по тендеру.
     * @return string
     */
    public function getResponsibleProfileName()
    {
        return !empty($this->responsibleProfile) ? (!empty($this->responsibleProfile->name) ? $this->responsibleProfile->name : $this->responsible->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTa()
    {
        return $this->hasOne(TendersApplications::class, ['id' => 'ta_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLr()
    {
        return $this->hasOne(LicensesRequests::class, ['id' => 'lr_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTenderLossReason()
    {
        return $this->hasOne(TendersLr::class, ['tender_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLossReason()
    {
        return $this->hasOne(TendersLossReasons::class, ['id' => 'lr_id'])->via('tenderLossReason');
    }

    /**
     * Возвращает наименование причины проигрыша.
     * @return string
     */
    public function getLossReasonName()
    {
        return !empty($this->lossReason) ? $this->lossReason->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTendersTp()
    {
        return $this->hasMany(TendersTp::class, ['tender_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTendersWe()
    {
        return $this->hasMany(TendersWe::class, ['tender_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTendersLogs()
    {
        return $this->hasMany(TendersLogs::class, ['tender_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTendersFiles()
    {
        return $this->hasMany(TendersFiles::class, ['tender_id' => 'id']);
    }

    /**
     * Делает выборку идентификаторов источника файлов тендера.
     * @return array
     */
    public function getTendersSourceIdsFiles()
    {
        return ArrayHelper::getColumn($this->tendersFiles, 'src_id', false);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWinner()
    {
        return $this->hasOne(TendersResults::class, ['tender_id' => 'id']);
    }
}
