<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\httpclient\Client;
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
 * @property int $lr_id Запрос лицензий
 * @property string $comment Комментарий
 *
 * @property string $createdByProfileName
 * @property string $createdByProfileEmail
 * @property string $tkName
 * @property string $lawName
 * @property string $stateName
 * @property string $responsibleProfileName
 * @property string $managerProfileName
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
 * @property TendersPlatforms $tp
 * @property LicensesRequests $lr
 * @property TendersLogs[] $tendersLogs
 * @property TendersTp[] $tendersTp
 * @property TendersFiles[] $tendersFiles
 * @property TendersWe[] $tendersWe
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

    const TENDERS_44_PAGE_COMMON = 20;
    const TENDERS_44_PAGE_FILES = 21;
    const TENDERS_44_PAGE_LOGS = 22;

    /**
     * Ссылки на страницы, с которых производится сбор информации
     */
    const URL_TENDER_223_COMMON = self::URL_SRC_MAIN . '/223/purchase/public/purchase/info/common-info.html';
    const URL_TENDER_223_FILES = self::URL_SRC_MAIN . '/223/purchase/public/purchase/info/documents.html';
    const URL_TENDER_223_LOGS = self::URL_SRC_MAIN . '/223/purchase/public/purchase/info/journal.html';

    const URL_TENDER_44_COMMON = self::URL_SRC_MAIN . '/epz/order/notice/ea44/view/common-info.html';
    const URL_TENDER_44_FILES = self::URL_SRC_MAIN . '/epz/order/notice/ea44/view/documents.html';
    const URL_TENDER_44_LOGS = self::URL_SRC_MAIN . '/epz/order/notice/ea44/view/event-journal.html';

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
            [['created_at', 'created_by', 'tk_id', 'state_id', 'org_id', 'fo_ca_id', 'tp_id', 'manager_id', 'responsible_id', 'placed_at', 'date_stop', 'date_sumup', 'date_auction', 'ta_id', 'is_notary_required', 'is_contract_edit', 'deferral', 'is_contract_approved', 'lr_id'], 'integer'],
            [['title', 'conditions', 'comment'], 'string'],
            [['date_complete', 'urlSource', 'we', 'findTool', 'ftInn', 'ftKpp', 'ftTitle'], 'safe'],
            [['amount_start', 'amount_offer', 'amount_fo', 'amount_fc'], 'number'],
            [['law_no'], 'string', 'max' => 20],
            [['oos_number'], 'string', 'max' => 25],
            [['revision'], 'string', 'max' => 3],
            [['fo_ca_name'], 'string', 'max' => 255],
            [['title', 'comment'], 'default', 'value' => null],
            [['amount_offer'], 'default', 'value' => 0],
            [['tk_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersKinds::className(), 'targetAttribute' => ['tk_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['manager_id' => 'id']],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::className(), 'targetAttribute' => ['org_id' => 'id']],
            [['responsible_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['responsible_id' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersStates::className(), 'targetAttribute' => ['state_id' => 'id']],
            [['ta_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersApplications::className(), 'targetAttribute' => ['ta_id' => 'id']],
            [['tp_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersPlatforms::className(), 'targetAttribute' => ['tp_id' => 'id']],
            [['lr_id'], 'exist', 'skipOnError' => true, 'targetClass' => LicensesRequests::className(), 'targetAttribute' => ['lr_id' => 'id']],
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
            'lr_id' => 'Запрос лицензий',
            'comment' => 'Комментарий',
            // виртуальные поля
            'urlSource' => 'Ссылка на закупку',
            'crudeWaste' => 'Отходы',
            'ftInn' => 'ИНН',
            'ftKpp' => 'КПП',
            'ftTitle' => 'Наименование закупки',
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

                // идентификация контрагента
                if (!empty($this->fo_ca_id)) {
                    $foCompany = foCompany::findOne($this->fo_ca_id);
                    if ($foCompany) {
                        $this->fo_ca_name = trim($foCompany->COMPANY_NAME);
                    }
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

            if (is_array($files) && count($files) > 0) {
                $this->obtainAuctionFiles($files);
            }
            unset($files);

            // также извлекаем историю изменений из журнала событий
            $lawNo++;
            $logs = $this->fetchTenderByNumber($lawNo);
            if (is_array($logs) && count($logs) > 0) {
                $this->obtainAuctionLogs($logs);
            }
            unset($logs);
        }
        else {
            if (isset($changedAttributes['state_id'])) {
                // проверим, изменился ли статус тендера
                if ($changedAttributes['state_id'] != $this->state_id) {
                    switch ($this->state_id) {
                        case TendersStates::STATE_СОГЛАСОВАНА:
                            // отправим специалистам тендерного отдела соответствующее уведомление
                            $letter = Yii::$app->mailer->compose([
                                'html' => 'tenderHasBeenApproved-html',
                            ], ['model' => $this])
                                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                                ->setTo('tender@1nok.ru')
                                ->setSubject('Участие в тендере согласовано руководством');
                            $letter->send();

                            break;
                        case TendersStates::STATE_ОТКАЗ:
                            // статус изменился на "Отказ", отправим автору тендера соответствующее уведомление
                            $receiver = $this->createdByProfileEmail;
                            if (!empty($receiver)) {
                                $letter = Yii::$app->mailer->compose([
                                    'html' => 'poReject-html',
                                ], ['model' => $this])
                                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                                    ->setTo($receiver)
                                    ->setSubject('По тендеру получен отказ');
                                $letter->send();
                            }
                            unset($receiver);

                            break;
                    }

                    $oldStateName = '';
                    $oldState = TendersStates::findOne($changedAttributes['state_id']);
                    if ($oldState != null) $oldStateName = ' с ' . $oldState->name;

                    (new TendersLogs([
                        'tender_id' => $this->id,
                        'description' => 'Изменение статуса' . $oldStateName . ' на ' . $this->stateName,
                    ]))->save();
                }
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
        $result = mb_substr($result, 6, 4) . '-' . mb_substr($result, 3, 2) . '-' . mb_substr($result, 0, 2) . ' ' . mb_substr($result, 11, 2) . ':' . mb_substr($result, 14, 2) . ':00';
        if (false != strtotime($result)) {
            return strtotime($result);
        }
        else {
            return null;
        }
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
     * Выкачивает файлы по ссылкам из массива. Если передается параметр $checkIfExists в значении true, то дополнительно
     * выполняется проверка существования файла (невозможно для новых).
     * @param $files array массив файлов, которые удастся обнаружить в момент вызова функции
     * @param bool $checkIfExists
     * @throws \yii\base\Exception
     * @throws \yii\httpclient\Exception
     */
    public function obtainAuctionFiles($files, $checkIfExists = false)
    {
        $existingFiles = [];
        if ($checkIfExists) {
            $existingFiles = $this->tendersSourceIdsFiles;
        }

        $client = new Client();
        $pifp = TendersFiles::getUploadsFilepath($this);

        foreach ($files as $file) {
            if (isset($file['url'])) {
                // проверка существования файла
                if ($checkIfExists && in_array($file['src_id'], $existingFiles)) {
                    // выполняется обновление файлов, обнаружен дубликат, пропускаем такой файл
                    continue;
                }

                $response = $client->get($file['url'], null, ['user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36 OPR/58.0.3135.118'])->send();
                if ($response->isOk) {
                    if (preg_match('~filename=(?|"([^"]*)"|\'([^\']*)\'|([^;]*))~', $response->headers['content-disposition'], $match)) {
                        // скачиваем файлы по очереди в папку для тендеров

                        $ofn = urldecode($match[1]);
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
                                'revision' => $file['revision'],
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

                            $model->save();
                        }

                        unset($fileAttached_fn);
                        unset($fileAttached_ffp);
                    }
                }
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
            'reject' => Html::submitButton('<i class="fa fa-times" aria-hidden="true"></i> Отказать', ['class' => 'btn btn-danger btn-lg', 'name' => 'reject', 'title' => 'Отказать в согласовании (обязательно нужно будет указать причину согласования)']),
            'revoke' => Html::submitButton('<i class="fa fa-repeat" aria-hidden="true"></i> Отозвать', ['class' => 'btn btn-danger btn-lg', 'name' => 'revoke']),
            'take_over' => Html::submitButton('Взять в работу', ['class' => 'btn btn-default btn-lg', 'name' => 'take_over']),
            'submitted' => Html::submitButton('Заявка подана', ['class' => 'btn btn-default btn-lg', 'name' => 'submitted']),
            'refinement' => Html::submitButton('Дозапрос', ['class' => 'btn btn-default btn-lg', 'name' => 'refinement']),
            'victory' => Html::submitButton('<i class="fa fa-trophy text-success" aria-hidden="true"></i> Победа', ['class' => 'btn btn-default btn-lg', 'name' => 'victory']),
            'defeat' => Html::submitButton('Проигрыш', ['class' => 'btn btn-default btn-lg', 'name' => 'defeat']),
            'abyss' => Html::submitButton('Без результатов', ['class' => 'btn btn-default btn-lg', 'name' => 'abyss']),
        ];

        $result = $controlButtons['index'] . ' ';

        if ($this->state_id <= TendersStates::STATE_ДОЗАПРОС && !$this->isNewRecord) {
            $result .= ' ' . $controlButtons['save'];
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
                if ($roleTenders) {
                    // специалист по тендерам может взять в работу
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
            case self::TENDERS_44_PAGE_COMMON:
                $url = self::URL_TENDER_44_COMMON;
                break;
            case self::TENDERS_44_PAGE_FILES:
                $url = self::URL_TENDER_44_FILES;
                break;
            case self::TENDERS_44_PAGE_LOGS:
                $url = self::URL_TENDER_44_LOGS;
                break;
        }

        if (!empty($this->oos_number) && !empty($url)) {
            $result = [
                'law_no' => $page,
            ];
            if ($page == self::TENDERS_44_PAGE_COMMON) {
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
                    $html = \keltstr\simplehtmldom\SimpleHTMLDom::str_get_html($response->content);
                    if ($html->innertext != '') {
                        switch ($page) {
                            case self::TENDERS_223_PAGE_LOGS:
                            case self::TENDERS_44_PAGE_LOGS:
                                ////////////////////////////////////////////////////////////////////////////////////////
                                /// СБОР СО СТРАНИЦЫ "ЖУРНАЛ СОБЫТИЙ"                                                ///
                                ////////////////////////////////////////////////////////////////////////////////////////
                                $logRows = $html->find('table#documentAction>tbody>tr');
                                if (count($logRows) > 0) {
                                    foreach ($logRows as $row) {
                                        $pizDate = trim($row->children(0)->plaintext);
                                        try {
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
                                                ////////////////////////////////////////////////////////////////////////////////
                                                /// СБОР СО СТРАНИЦЫ "ОБЩАЯ ИНФОРМАЦИЯ"                                      ///
                                                ////////////////////////////////////////////////////////////////////////////////
                                                switch ($chapterText) {
                                                    case 'сведения о закупке':
                                                    case 'общие сведения о закупке':
                                                    case 'общая информация о закупке':
                                                        // nextSibling() для h2 вернет <div class="noticeTabBoxWrapper">
                                                        // children(0) вернет <table>
                                                        // children(0) вернет <tbody>
                                                        $properties = $chapterTitle->nextSibling()->children(0)->children(0);
                                                        foreach ($properties->children() as $tr) {
                                                            // перебор <tr>
                                                            foreach ($tr->children() as $td) {
                                                                // перебираем ячейки в строке таблицы
                                                                $fieldText = trim(mb_strtolower($td->plaintext));
                                                                $fieldText = preg_replace('| +|', ' ', $fieldText); // множество пробелов заменяем одним
                                                                $fieldText = str_replace('«', '', $fieldText);
                                                                $fieldText = str_replace('»', '', $fieldText);
                                                                $fieldText = str_replace('"', '', $fieldText);
                                                                //$result['name'] .= chr(13) . $fieldText;
                                                                switch ($fieldText) {
                                                                    case 'реестровый номер извещения':
                                                                        $result['regNumber'] = trim($td->nextSibling()->plaintext);
                                                                        break;
                                                                    case 'способ размещения закупки':
                                                                    case 'способ определения поставщика (подрядчика, исполнителя)':
                                                                        $result['placeMethod'] = trim($td->nextSibling()->plaintext);
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
                                                                        $result['name'] = trim($td->nextSibling()->plaintext);
                                                                        break;
                                                                    case 'наименование электронной площадки в информационно-телекоммуникационной сети интернет':
                                                                        $result['tp_name'] = trim($td->nextSibling()->plaintext);
                                                                        break;
                                                                    case 'адрес электронной площадки в информационно-телекоммуникационной сети интернет':
                                                                        $result['tp_url'] = trim($td->nextSibling()->plaintext);
                                                                        break;
                                                                    case 'редакция':
                                                                        $result['revision'] = trim($td->nextSibling()->plaintext);
                                                                        break;
                                                                    case 'дата размещения извещения':
                                                                        $pizDate = trim($td->nextSibling()->plaintext);
                                                                        $pizDate = mb_substr($pizDate, 6, 4) . '-' . mb_substr($pizDate, 3, 2) . '-' . mb_substr($pizDate, 0, 2) . ' 04:00:00';
                                                                        $result['placed_at_f'] = Yii::$app->formatter->asDate($pizDate, 'php:d.m.Y');
                                                                        $result['placed_at_u'] = strtotime($pizDate);
                                                                        unset($pizDate);

                                                                        break;
                                                                }
                                                            }
                                                        }

                                                        break;
                                                    case 'заказчик':
                                                        // описание см. выше
                                                        $properties = $chapterTitle->nextSibling()->children(0);
                                                        foreach ($properties->children() as $tr) {
                                                            foreach ($tr->children() as $td) {
                                                                // перебираем ячейки в строке таблицы
                                                                $fieldText = trim(mb_strtolower($td->plaintext));
                                                                switch ($fieldText) {
                                                                    case 'наименование организации':
                                                                        $result['customer_name'] = Html::decode(trim($td->nextSibling()->plaintext));
                                                                        break;
                                                                    case 'инн':
                                                                        $result['customer_inn'] = trim($td->nextSibling()->plaintext);
                                                                        break;
                                                                    case 'кпп':
                                                                        $result['customer_kpp'] = trim($td->nextSibling()->plaintext);
                                                                        break;
                                                                    case 'огрн':
                                                                        $result['customer_ogrn'] = trim($td->nextSibling()->plaintext);
                                                                        break;
                                                                    case 'место нахождения':
                                                                        $result['customer_address_f'] = trim($td->nextSibling()->plaintext);
                                                                        break;
                                                                    case 'почтовый адрес':
                                                                        $result['customer_address_m'] = trim($td->nextSibling()->plaintext);
                                                                        break;
                                                                }
                                                            }
                                                        }

                                                        break;
                                                    case 'информация об организации, осуществляющей определение поставщика (подрядчика, исполнителя)':
                                                        // описание см. выше
                                                        $properties = $chapterTitle->nextSibling()->children(0)->children(0);
                                                        foreach ($properties->children() as $tr) {
                                                            foreach ($tr->children() as $td) {
                                                                // перебираем ячейки в строке таблицы
                                                                $fieldText = trim(mb_strtolower($td->plaintext));
                                                                switch ($fieldText) {
                                                                    case 'организация, осуществляющая размещение':
                                                                        if (trim($td->nextSibling()->plaintext) === '') {
                                                                            // пустой комментарий присутствует в разметке, учтем это
                                                                            $customer_name = Html::decode(trim($td->nextSibling()->nextSibling()->plaintext));
                                                                        }
                                                                        else {
                                                                            $customer_name = Html::decode(trim($td->nextSibling()->plaintext));
                                                                        }
                                                                        $result['customer_name'] = $customer_name;
                                                                        break 3; // вообще уходим отсюда, потому что из этого блока нам нужно только наименование заказчика
                                                                }
                                                            }
                                                        }

                                                        break;
                                                    case 'порядок проведения процедуры':
                                                    case 'информация о процедуре закупки':
                                                        // описание см. выше
                                                        $properties = $chapterTitle->nextSibling()->children(0)->children(0);
                                                        foreach ($properties->children() as $tr) {
                                                            foreach ($tr->children() as $td) {
                                                                // перебираем ячейки в строке таблицы
                                                                $fieldText = trim(mb_strtolower($td->plaintext));
                                                                $fieldText = preg_replace('| +|', ' ', $fieldText); // множество пробелов заменяем одним
                                                                $fieldText = str_replace(' </br>', '', $fieldText);
                                                                switch ($fieldText) {
                                                                    case 'дата и время окончания подачи заявок (по местному времени заказчика)':
                                                                    case 'дата и время окончания подачи заявок':
                                                                        // 223: 05.09.2019 в 14:00 (МСК)
                                                                        // 44: 24.09.2019 10:00
                                                                        $pizDate = trim($td->nextSibling()->plaintext);
                                                                        if ($page == self::TENDERS_223_PAGE_COMMON) {
                                                                            $pizDate = mb_substr($pizDate, 6, 4) . '-' . mb_substr($pizDate, 3, 2) . '-' . mb_substr($pizDate, 0, 2) . ' ' . mb_substr($pizDate, 13, 2) . ':' . mb_substr($pizDate, 16, 2) . ':00';
                                                                        }
                                                                        else {
                                                                            $pizDate = mb_substr($pizDate, 6, 4) . '-' . mb_substr($pizDate, 3, 2) . '-' . mb_substr($pizDate, 0, 2) . ' ' . mb_substr($pizDate, 11, 2) . ':' . mb_substr($pizDate, 14, 2) . ':00';
                                                                        }
                                                                        $result['pf_date_f'] = Yii::$app->formatter->asDate($pizDate, 'php:d.m.Y H:i');
                                                                        $result['pf_date_u'] = strtotime($pizDate);
                                                                        // еще вариант, менее надежный, потому что необходимо наверняка знать часовой пояс
                                                                        //$pizDate = \DateTime::createFromFormat('d.m.Y в H:i (МСК)', $pizDate)->format('Y-m-d H:i');
                                                                        unset($pizDate);

                                                                        break;
                                                                    case 'дата подведения итогов':
                                                                        $pizDate = trim($td->nextSibling()->plaintext);
                                                                        $pizDate = mb_substr($pizDate, 6, 4) . '-' . mb_substr($pizDate, 3, 2) . '-' . mb_substr($pizDate, 0, 2) . ' 04:00:00';
                                                                        $result['su_date_f'] = Yii::$app->formatter->asDate($pizDate, 'php:d.m.Y');
                                                                        $result['su_date_u'] = strtotime($pizDate);
                                                                        unset($pizDate);

                                                                        break;
                                                                    case 'дата и время начала подачи заявок':
                                                                        $pizDate = trim($td->nextSibling()->plaintext);
                                                                        $pizDate = mb_substr($pizDate, 6, 4) . '-' . mb_substr($pizDate, 3, 2) . '-' . mb_substr($pizDate, 0, 2) . ' 04:00:00';
                                                                        $result['placed_at_f'] = Yii::$app->formatter->asDate($pizDate, 'php:d.m.Y');
                                                                        $result['placed_at_u'] = strtotime($pizDate);
                                                                        unset($pizDate);

                                                                        break;
                                                                    case 'дата проведения аукциона в электронной форме':
                                                                        $pizDate = trim($td->nextSibling()->plaintext);
                                                                        $pizTime = trim($tr->nextSibling()->children(1)->plaintext);
                                                                        $pizDate = mb_substr($pizDate, 6, 4) . '-' . mb_substr($pizDate, 3, 2) . '-' . mb_substr($pizDate, 0, 2) . ' ' . $pizTime;
                                                                        $result['auction_at_f'] = Yii::$app->formatter->asDate($pizDate, 'php:d.m.Y H:i');
                                                                        $result['auction_at_u'] = strtotime($pizDate);
                                                                        unset($pizDate);

                                                                        break;
                                                                }
                                                            }
                                                        }

                                                        break;
                                                    case 'начальная (максимальная) цена контракта':
                                                        // описание см. выше
                                                        $properties = $chapterTitle->nextSibling()->children(0)->children(0);
                                                        foreach ($properties->children() as $tr) {
                                                            foreach ($tr->children() as $td) {
                                                                // перебираем ячейки в строке таблицы
                                                                $fieldText = trim(mb_strtolower($td->plaintext));
                                                                switch ($fieldText) {
                                                                    case 'начальная (максимальная) цена контракта':
                                                                        $price = trim($td->nextSibling()->plaintext);
                                                                        $price = str_replace([' ', chr(0xC2) . chr(0xA0)], '', $price);
                                                                        $price = str_replace(',', '.', $price);
                                                                        $result['price'] = $price;
                                                                        break;
                                                                }
                                                            }
                                                        }

                                                        break;
                                                    case 'обеспечение заявки':
                                                    case 'обеспечение исполнения контракта':
                                                        // описание см. выше
                                                        $properties = $chapterTitle->nextSibling()->children(0);
                                                        foreach ($properties->children() as $tr) {
                                                            foreach ($tr->children() as $td) {
                                                                // перебираем ячейки в строке таблицы
                                                                $fieldName = 'amount_fc';
                                                                $fieldText = trim(mb_strtolower($td->plaintext));
                                                                switch ($fieldText) {
                                                                    case 'размер обеспечения заявки':
                                                                        $fieldName = 'amount_fo';
                                                                    case 'размер обеспечения исполнения контракта':
                                                                        $amount = trim($td->nextSibling()->plaintext);
                                                                        $amount = str_replace('Российский рубль', '', $amount);
                                                                        $amount = str_replace([' ', chr(0xC2) . chr(0xA0)], '', $amount);
                                                                        if (false !== strpos($amount, '%') && !empty($result['price'])) {
                                                                            $amount = $amount * $result['price'] / 100;
                                                                        }
                                                                        else {
                                                                            $amount = floatval(str_replace(',', '.', $amount));
                                                                        }

                                                                        $result[$fieldName] = $amount;
                                                                        unset($amount);

                                                                        break;
                                                                }
                                                                unset($fieldName);
                                                            }
                                                        }
                                                        break;
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
                                                        // nextSibling() - это div, children(0)->children(0) - это table > tbody
                                                        $block = $chapterTitle->nextSibling()->children(0)->children(0);
                                                        foreach ($block->children() as $tr) {
                                                            if (!empty($tr->children(3))) {
                                                                try {
                                                                    $uploaded_at = $this->convertDate($tr->children(3)->children(0)->children(1)->plaintext);
                                                                    // children(4) - это последний в строке td, children(1) - это <div class=" attachment">, children(1) - это <div class=" displayTable">, children(0) - это <a>
                                                                    $attachments = $tr->children(4)->children();
                                                                    if (count($attachments) > 0) {
                                                                        foreach ($attachments as $attachment) {
                                                                            // перебираем строки с ссылками на файлы
                                                                            if ($attachment->attr['class'] != 'attachment') continue; // некоторые сразу пропускаем, они не содержат ссылок на файлы

                                                                            $link = $attachment->children(1)->children(0);
                                                                            $src_id = null;
                                                                            if (preg_match("/.*\?uid=(\w+)$/", $link->attr['href'], $output_array)){
                                                                                $src_id = $output_array[1];
                                                                                unset($output_array);
                                                                            }

                                                                            $ct_id = null;
                                                                            switch ($chapterText) {
                                                                                case 'документация, изменение документации':
                                                                                    $ct_id = TendersContentTypes::CONTENT_TYPE_ДОКУМЕНТАЦИЯ; break;
                                                                                case 'разъяснение положений документации об электронном аукционе':
                                                                                    $ct_id = TendersContentTypes::CONTENT_TYPE_РАЗЪЯСНЕНИЯ_ДОКУМЕНТАЦИИ; break;
                                                                            }

                                                                            $result[] = [
                                                                                'url' => $link->attr['href'],
                                                                                'src_id' => $src_id,
                                                                                'uploaded_at' => $uploaded_at, // дата размещения на площадке, одна на все файлы данной строки
                                                                                'ct_id' => $ct_id,
                                                                            ];
                                                                        }
                                                                    }
                                                                }
                                                                catch (\Exception $exception) {}
                                                            }
                                                        }

                                                        break;
                                                    case 'протоколы работы комиссии, полученные с электронной площадки (эп)':
                                                        // nextSibling() - это div, children(0)->children(0) - это table > tbody
                                                        $block = $chapterTitle->nextSibling()->children(0)->children(0);
                                                        // перебираем строки с ссылками на файлы
                                                        if (count($block->children()) > 1) {
                                                            foreach ($block->children() as $tr) {
                                                                // ссылка на файл
                                                                $url = null;
                                                                if (isset($tr->children(3)->children(1)->children(1)->children(0)->attr['href'])) {
                                                                    // children(3) - это последний в строке td, children(1) - это <div class=" attachment">, children(1) - это <div class=" width200 w-wrap-break-word minWidth200-pv word-wrap-break-word-pv">, children(0) - это <a>
                                                                    $url = $tr->children(3)->children(1)->children(1)->children(0)->attr['href'];
                                                                }

                                                                $result[] = [
                                                                    'url' => $url,
                                                                    'uploaded_at' => $this->convertDate($tr->children(2)->children(0)->children(1)->plaintext), // дата размещения на площадке
                                                                    'ct_id' => TendersContentTypes::CONTENT_TYPE_ПРОТОКОЛЫ,
                                                                ];

                                                                unset($pizDate);
                                                            }
                                                        }

                                                        break;
                                                }

                                                break;
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
        return $this->hasOne(TendersKinds::className(), ['id' => 'tk_id']);
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
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByRole()
    {
        return $this->hasOne(AuthAssignment::className(), ['user_id' => 'manager_id']);
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
        return $this->hasOne(AuthAssignment::className(), ['user_id' => 'manager_id']);
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
    public function getTendersTp()
    {
        return $this->hasMany(TendersTp::class, ['tender_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTendersWe()
    {
        return $this->hasMany(TendersWe::className(), ['tender_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTendersLogs()
    {
        return $this->hasMany(TendersLogs::className(), ['tender_id' => 'id']);
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
}
