<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * This is the model class for table "edf".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $type_id Тип документа
 * @property int $parent_id Корневой документ (договор к допсоглашению, например)
 * @property int $ct_id Вид договора
 * @property int $state_id Статус документа
 * @property int $org_id Организация
 * @property int $ba_id Банковский счет
 * @property int $manager_id Ответственный менеджер
 * @property int $cp_id Пакет корреспонденции
 * @property int $fo_ca_id Контрагент из Fresh Office
 * @property int $is_typical_form Типовая форма документа
 * @property string $doc_num Номер документа
 * @property string $doc_date Дата документа
 * @property string $doc_date_expires Дата окончания документа
 * @property string $amount Сумма договора
 * @property string $basis Основание
 * @property string $req_name_full Полное наименование контрагента
 * @property string $req_name_short Сокращенное наименование контрагента
 * @property string $req_ogrn ОГРН контрагента
 * @property string $req_inn ИНН контрагента
 * @property string $req_kpp КПП контрагента
 * @property string $req_address_j Юридический адрес контрагента
 * @property string $req_address_f Фактический адрес контрагента
 * @property string $req_an Номер банковского счета контрагента
 * @property string $req_bik БИК банка контрагента
 * @property string $req_bn Наименование банка контрагента
 * @property string $req_ca Корреспондентский счет контрагента
 * @property string $req_phone Номер телефона контрагента
 * @property string $req_email E-mail контрагента
 * @property string $req_dir_post Должность директора контрагента (им. падеж)
 * @property string $req_dir_name ФИО директора контрагента полностью (им. падеж)
 * @property string $req_dir_name_of ФИО директора контрагента полностью (род. падеж)
 * @property string $req_dir_name_short ФИО директора контрагента сокрщенно (им. падеж)
 * @property string $req_dir_name_short_of ФИО директора контрагента сокращенно (род. падеж)
 * @property int $is_received_scan Скан-копии получены
 * @property int $is_received_original Оригинал сдан в бухгалтерию
 * @property string $files_full_path Полный путь к папке для хранения файлов электронного документа
 * @property string $comment Комментарий
 * @property string $reject_reason Причина отказа
 * @property int $ppdq Количество дней постоплаты
 * @property int $deferral_type Тип дней отсрочки (1 - банковские, 2 - календарные)
 *
 * @property string $createdByProfileName
 * @property string $typeName
 * @property string $contractTypeName
 * @property string $stateName
 * @property string $organizationName
 * @property string $bankAccountNumber
 * @property string $managerProfileName
 * @property string $managerEmail
 *
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property AuthAssignment $createdByRoles
 * @property DocumentsTypes $type
 * @property Edf $parent
 * @property ContractTypes $contractType
 * @property EdfStates $state
 * @property Organizations $organization
 * @property OrganizationsBas $bankAccount
 * @property User $manager
 * @property Profile $managerProfile
 * @property AuthAssignment $managerRoles
 * @property CorrespondencePackages $cp
 * @property EdfFiles[] $edfFiles
 * @property EdfStatesHistory[] $edfStatesHistories
 * @property EdfTp[] $tablePart
 */
class Edf extends \yii\db\ActiveRecord
{
    /**
     * Возможные значения для поля "Тип дней острочки"
     */
    const DEFERRAL_TYPE_БАНКОВСКИЕ = 1;
    const DEFERRAL_TYPE_КАЛЕНДАРНЫЕ = 2;

    /**
     * Возможные значения для полей отбора
     */
    const FILTER_TYPICAL_ТИПОВЫЕ = 1;
    const FILTER_TYPICAL_КАСТОМНЫЕ = 2;

    const FILTER_SCAN_ЕСТЬ = 1;
    const FILTER_SCAN_НЕТ = 2;

    const FILTER_ORIGINAL_ЕСТЬ = 1;
    const FILTER_ORIGINAL_НЕТ = 2;

    /**
     * Псевдонимы присоединяемых таблиц
     */
    const JOIN_CREATOR_PROFILE_ALIAS = 'createdByProfile';
    const JOIN_CREATOR_ROLES_ALIAS = 'createdByRoles';
    const JOIN_MANAGER_PROFILE_ALIAS = 'managerProfile';
    const JOIN_MANAGER_ROLES_ALIAS = 'managerRoles';

    /**
     * @var array табличная часть
     */
    public $tp;

    /**
     * @var array массив ошибок при заполнении табличной части
     */
    public $tpErrors;

    /**
     * @var array прикрепленные к заявке файлы
     */
    public $initialFiles;

    /**
     * @var integer дата и время приобретения текущего статуса
     */
    public $stateChangedAt;

    /**
     * @var integer количество непрочитанных сообщений в диалогах документа
     */
    public $unreadMessagesCount;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'edf';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // обязательно для всех
            [['type_id', 'state_id', 'org_id', 'manager_id', 'doc_num', 'doc_date', 'fo_ca_id'], 'required'],
            // обязательно для договоров
            [['req_inn', 'req_ogrn'], 'required', 'on' => 'creatingContract'],
            [['initialFiles'], 'required', 'on' => 'creatingContract', 'message' => 'Для выбранного типа документа требуется загрузка минимум одного файла'],
            [['ca_name', 'ca_contact_person', 'basis', 'req_name_full', 'req_name_short', 'req_ogrn', 'req_inn', 'req_kpp', 'req_address_j', 'req_address_f', 'req_bik', 'req_bn', 'req_phone', 'req_email', 'req_dir_post', 'req_dir_name'], 'required', 'on' => 'savingContract'],
            // обязательно для допсоглашения
            //[['parent_id'], 'required', 'on' => 'creatingAgreement'],
            // обязательно для передачи на согласование менеджеру
            [['req_bik', 'req_bn'], 'required', 'on' => 'approvingDocument'],
            [['created_at', 'created_by', 'type_id', 'parent_id', 'ct_id', 'state_id', 'org_id', 'ba_id', 'manager_id', 'cp_id', 'fo_ca_id', 'ppdq', 'deferral_type'], 'integer'],
            [['is_typical_form', 'is_received_scan', 'is_received_original'], 'boolean'],
            [['doc_date', 'doc_date_expires'], 'safe'],
            [['files_full_path', 'comment', 'reject_reason'], 'string'],
            [['doc_num', 'basis', 'req_name_full', 'req_name_short', 'req_ogrn', 'req_inn', 'req_kpp', 'req_address_j', 'req_address_f', 'req_bn', 'req_phone', 'req_email', 'req_dir_post', 'req_dir_name', 'req_dir_name_of', 'req_dir_name_short', 'req_dir_name_short_of'], 'string', 'max' => 255],
            [['req_an', 'req_ca'], 'string', 'max' => 25],
            [['req_bik'], 'string', 'max' => 10],
            [['amount'], 'number'],
            [[
                'req_name_full', 'req_name_short', 'req_ogrn', 'req_inn', 'req_kpp', 'req_address_j', 'req_address_f',
                'req_an', 'req_bik', 'req_bn', 'req_ca', 'req_phone', 'req_email', 'req_dir_post', 'req_dir_name',
                'req_dir_name_of', 'req_dir_name_short', 'req_dir_name_short_of'
            ], 'default', 'value' => null],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['manager_id' => 'id']],
            [['cp_id'], 'exist', 'skipOnError' => true, 'targetClass' => CorrespondencePackages::class, 'targetAttribute' => ['cp_id' => 'id']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Edf::class, 'targetAttribute' => ['parent_id' => 'id']],
            [['ba_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationsBas::class, 'targetAttribute' => ['ba_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['ct_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContractTypes::class, 'targetAttribute' => ['ct_id' => 'id']],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['org_id' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => EdfStates::class, 'targetAttribute' => ['state_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DocumentsTypes::class, 'targetAttribute' => ['type_id' => 'id']],
            [['initialFiles'], 'file', 'skipOnEmpty' => true, 'skipOnError' => false, 'maxFiles' => 10],
            // собственные правила валидации
            ['type_id', 'validateType'],
            ['tp', 'validateTp'],
            ['state_id', 'validateState'],
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
            'created_by' => 'Автор создания',
            'type_id' => 'Тип документа',
            'parent_id' => 'Корневой документ', // договор к допсоглашению, например
            'ct_id' => 'Вид договора',
            'state_id' => 'Статус документа',
            'org_id' => 'Организация',
            'ba_id' => 'Банковский счет',
            'manager_id' => 'Ответственный менеджер',
            'cp_id' => 'Пакет корреспонденции',
            'fo_ca_id' => 'Контрагент из Fresh Office',
            'is_typical_form' => 'Типовая форма документа',
            'doc_num' => 'Номер документа',
            'doc_date' => 'Дата документа',
            'doc_date_expires' => 'Дата окончания документа',
            'amount' => 'Сумма договора',
            'basis' => 'Основание',
            'req_name_full' => 'Полное наименование контрагента',
            'req_name_short' => 'Сокращенное наименование контрагента',
            'req_ogrn' => 'ОГРН контрагента',
            'req_inn' => 'ИНН контрагента',
            'req_kpp' => 'КПП контрагента',
            'req_address_j' => 'Юридический адрес контрагента',
            'req_address_f' => 'Фактический адрес контрагента',
            'req_an' => 'Номер банковского счета контрагента',
            'req_bik' => 'БИК банка контрагента',
            'req_bn' => 'Наименование банка контрагента',
            'req_ca' => 'Корреспондентский счет контрагента',
            'req_phone' => 'Номер телефона контрагента',
            'req_email' => 'E-mail контрагента',
            'req_dir_post' => 'Должность директора контрагента',
            'req_dir_name' => 'ФИО директора контрагента полностью (им. падеж)',
            'req_dir_name_of' => 'ФИО директора контрагента полностью (род. падеж)',
            'req_dir_name_short' => 'ФИО директора контрагента сокрщенно (им. падеж)',
            'req_dir_name_short_of' => 'ФИО директора контрагента сокращенно (род. падеж)',
            'is_received_scan' => 'Скан-копии получены',
            'is_received_original' => 'Оригинал сдан в бухгалтерию',
            'files_full_path' => 'Полный путь к папке для хранения файлов электронного документа',
            'comment' => 'Комментарий',
            'reject_reason' => 'Причина отказа',
            'ppdq' => 'Количество дней постоплаты',
            'deferral_type' => 'Тип дней отсрочки (1 - банковские, 2 - календарные)',
            // виртуальные поля
            'tp' => 'Отходы',
            'initialFiles' => 'Файлы',
            'stateChangedAt' => 'Статус приобретен',
            // вычисляемые поля
            'createdByProfileName' => 'Автор создания',
            'typeName' => 'Тип документа',
            'parentRep' => 'Корневой документ',
            'contractTypeName' => 'Вид договора',
            'stateName' => 'Статус документа',
            'organizationName' => 'Организация',
            'bankAccountNumber' => 'Банковский счет',
            'baRep' => 'Банковский счет',
            'managerName' => 'Ответственный менеджер',
            'managerProfileName' => 'Ответственный',
            'cpRep' => 'Пакет корреспонденции',
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
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $cleanName = DadataAPI::cleanName($this->req_dir_name);
                if (!empty($cleanName)) {
                    $this->req_dir_name_of = $cleanName['result_genitive'];
                    // сокращенные ФИО директора в именительном падеже
                    $this->req_dir_name_short = $cleanName['surname'] .
                        (!empty($cleanName['name']) ? ' ' . mb_substr($cleanName['name'], 0, 1) . '.' : '') .
                        (!empty($cleanName['patronymic']) ? ' ' . mb_substr($cleanName['patronymic'], 0, 1) . '.' : '');

                    // просклоняем сокращенные ФИО
                    $cleanShortName = DadataAPI::cleanName($this->req_dir_name_short);
                    if (!empty($cleanShortName) && isset($cleanShortName['result_genitive'])) {
                        $this->req_dir_name_short_of = $cleanShortName['result_genitive'];
                    }
                    else {
                        // не удалось просклонять, просто берем сокращенные ФИО
                        $this->req_dir_name_short_of = $this->req_dir_name_short;
                    }
                }
                else {
                    // не удалось просклонять, просто берем полные ФИО
                    $this->req_dir_name_of = $this->req_dir_name;
                }

                // должность директора
                $this->req_dir_post = DadataAPI::mb_ucfirst($this->req_dir_post);
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            // один раз при создании указываем в какой папке будут храниться файлы этого электронного документа
            $this->updateAttributes([
                'files_full_path' => EdfFiles::getUploadsFilepath() . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $this->id,
            ]);
        }
        else {
            if (isset($changedAttributes['state_id'])) {
                // проверим, изменился ли статус пакета корреспонденции
                if ($changedAttributes['state_id'] != $this->state_id) {
                    $oldStateName = '';
                    $oldState = EdfStates::findOne($changedAttributes['state_id']);
                    if ($oldState != null) $oldStateName = ' с ' . $oldState->name;

                    $cpHistoryModel = new EdfStatesHistory([
                        'created_by' => Yii::$app->user->id,
                        'ed_id' => $this->id,
                        'description' => 'Изменение статуса' . $oldStateName . ' на ' . $this->stateName,
                    ]);
                    $cpHistoryModel->save();

                    // уведомление на почту менеджеру о том, что документ дошел до этапа согласования
                    if ($this->state_id == EdfStates::STATE_СОГЛАСОВАНИЕ) {
                        $receiver = $this->managerEmail;
                        if (!empty($receiver)) {
                            $letter = Yii::$app->mailer->compose([
                                'html' => 'edfApproved-html',
                            ], [
                                'edf' => $this,
                                'body' => 'Во вложении документы' . (!empty($this->req_name_full) ? ' для компании <strong>' . $this->req_name_full . '</strong>' : ' комплекта № ' . $this->id) . ', которые необходимо согласовать.',
                            ])->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderNameSvetozar']])
                                ->setTo($receiver)
                                ->setSubject('На согласование поступил пакет документов');

                            foreach ($this->edfFiles as $attach) {
                                $letter->attach($attach->ffp, ['fileName' => $attach->ofn]);
                            }

                            $letter->send();
                        }
                    }
                }
            }
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
            $files = EdfFiles::find()->where(['ed_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            // удаляем табличную часть
            EdfTp::deleteAll(['ed_id' => $this->id]);

            // удаляем историю изменения статусов
            EdfStatesHistory::deleteAll(['ed_id' => $this->id]);

            // удаляем диалоги
            EdfDialogs::deleteAll(['ed_id' => $this->id]);

            return true;
        }

        return false;
    }

    /**
     * Возвращает число прописью. Не привязано к финансам.
     * @param $sourceNumber double исходное число
     * @return string
     */
    public static function spellNumberToRussian($sourceNumber)
    {
        // Целое значение $sourceNumber вывести прописью по-русски
        // Максимальное значение для аругмента-числа PHP_INT_MAX
        // Максимальное значение для аругмента-строки минус/плюс 999999999999999999999999999999999999
        $smallNumbers = [ //Числа 0..999
            ['ноль'],
            ['','один','два','три','четыре','пять','шесть','семь','восемь','девять'],
            ['десять','одиннадцать','двенадцать','тринадцать','четырнадцать',
                'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать'],
            ['','','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'],
            ['','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'],
            ['','одна','две']
        ];
        $degrees = [
            ['дофигальон','','а','ов'], // обозначение для степеней больше, чем в списке
            ['тысяч','а','и',''], //10^3
            ['миллион','','а','ов'], //10^6
            ['миллиард','','а','ов'], //10^9
            ['триллион','','а','ов'], //10^12
            ['квадриллион','','а','ов'], //10^15
            ['квинтиллион','','а','ов'], //10^18
            ['секстиллион','','а','ов'], //10^21
            ['септиллион','','а','ов'], //10^24
            ['октиллион','','а','ов'], //10^27
            ['нониллион','','а','ов'], //10^30
            ['дециллион','','а','ов'] //10^33
        ];

        if ($sourceNumber == 0) return $smallNumbers[0][0]; //Вернуть ноль
        $sign = '';
        if ($sourceNumber < 0) {
            $sign = 'минус '; // Запомнить знак, если минус
            $sourceNumber = substr($sourceNumber, 1);
        }
        $result = []; // Массив с результатом

        // Разложение строки на тройки цифр
        $digitGroups = array_reverse(str_split(str_pad($sourceNumber,ceil(strlen($sourceNumber)/3)*3,'0',STR_PAD_LEFT),3));
        foreach ($digitGroups as $key=>$value){
            $result[$key] = [];
            //Преобразование трёхзначного числа прописью по-русски
            foreach ($digit=str_split($value) as $key3=>$value3) {
                if (!$value3) continue;
                else {
                    switch ($key3) {
                        case 0:
                            $result[$key][] = $smallNumbers[4][$value3];
                            break;
                        case 1:
                            if ($value3==1) {
                                $result[$key][]=$smallNumbers[2][$digit[2]];
                                break 2;
                            }
                            else $result[$key][]=$smallNumbers[3][$value3];
                            break;
                        case 2:
                            if (($key==1)&&($value3<=2)) $result[$key][]=$smallNumbers[5][$value3];
                            else $result[$key][]=$smallNumbers[1][$value3];
                            break;
                    }
                }
            }
            $value *= 1;
            if (!$degrees[$key]) $degrees[$key]=reset($degrees);

            // Учесть окончание слов для русского языка
            if ($value && $key) {
                $index = 3;
                if (preg_match("/^[1]$|^\\d*[0,2-9][1]$/",$value)) $index = 1; //*1, но не *11
                else if (preg_match("/^[2-4]$|\\d*[0,2-9][2-4]$/",$value)) $index = 2; //*2-*4, но не *12-*14
                $result[$key][]=$degrees[$key][0].$degrees[$key][$index];
            }
            $result[$key] = implode(' ',$result[$key]);
        }

        return $sign.implode(' ', array_reverse($result));
    }

    /**
     * Собственное правило валидации для типа документа.
     * {@inheritdoc}
     */
    public function validateType()
    {
        if ($this->type_id == DocumentsTypes::TYPE_ДОГОВОР && empty($this->ct_id)) {
            $this->addError('ct_id', 'Поле обязательно для заполнения.');
        }
    }

    /**
     * Собственное правило валидации для табличной части.
     */
    public function validateTp()
    {
        if (count($this->tp) > 0) {
            $row_numbers = [];
            $iterator = 1;
            foreach ($this->tp as $index => $item) {
                $model = new EdfTp();
                $model->attributes  = $item;
                if (!$model->validate(['fkko_name', 'hk_id'])) {
                    $row_numbers[] = $iterator;
                }
                $iterator++;
            }
            if (count($row_numbers) > 0) $this->addError('tpErrors', 'Не все обязательные поля в табличной части заполнены! Строки: '.implode(', ', $row_numbers).'.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateState()
    {
        switch ($this->state_id) {
            case EdfStates::STATE_СОГЛАСОВАНИЕ:
                // закомментировано по просьбе заказчика 2019-05-27
                /*
                if ($this->getEdfFiles()->count() == 1)
                    $this->addError('state_id', 'Невозможно перевести в статус "Согласование" заявку, к которой не сформированы документы');
                */
                break;
            case EdfStates::STATE_ОТКАЗ:
                if (empty($this->reject_reason))
                    $this->addError('reject_reason', 'Заполните причину отказа.');
                break;
        }
    }

    /**
     * Превращает данные из массива идентификаторов в массив моделей EdfTp.
     * @return array
     */
    public function makeTpModelsFromPostArray()
    {
        $result = [];
        if (is_array($this->tp)) if (count($this->tp) > 0) {
            // в цикле заполним массив моделями строк
            foreach ($this->tp as $index => $item) {
                $dtp = new EdfTp();
                $dtp->attributes = $item;
                $dtp->ed_id = $this->id;
                $result[] = $dtp;
            }
        }

        return $result;
    }

    /**
     * Вычисляет следующий номер документа по шаблону из карточки организации.
     */
    public function calcNextDocumentNumber()
    {
        if (!empty($this->organization) && !empty($this->organization->doc_num_tmpl)) {
            if (preg_match("/\[C\d+\]/i", $this->organization->doc_num_tmpl, $matches)) {
                if (!empty($matches[0])) {
                    $docNum = '';
                    try {
                        $count = Edf::find()->where(['org_id' => $this->org_id, 'type_id' => DocumentsTypes::TYPE_ДОГОВОР])->andWhere('state_id > ' . EdfStates::STATE_ЧЕРНОВИК)->count();
                        $count++;
                        $docNum = str_replace($matches[0], str_pad($count, Drivers::leaveOnlyDigits($matches[0]), '0', STR_PAD_LEFT), $this->organization->doc_num_tmpl);
                        $docNum = str_replace('[M]', Yii::$app->formatter->asDate(time(), 'php:m'), $docNum);
                        $docNum = str_replace('[Y]', Yii::$app->formatter->asDate(time(), 'php:y'), $docNum);
                    }
                    catch (\Exception $e) {}

                    $this->doc_num = $docNum;
                }
            }
        }
    }

    /**
     * @param $word
     * @return mixed|string
     */
    public function dirPostDeclension($word)
    {
        $result = mb_strtolower($word);
        $result = str_replace('генеральный', 'генерального', $result);
        $result = str_replace('директор', 'директора', $result);
        $result = str_replace('директораа', 'директора', $result);
        $result = str_replace('президент', 'президента', $result);
        $result = str_replace('управляющий', 'управляющего', $result);

        return $result;
    }

    /**
     * Выполняет загрузку файлов из post-параметров, а также сохранение их имен в базу данных.
     * Каждый файл прикрепляется к текущему электронному документу.
     * @return array|bool
     * @throws \yii\base\Exception
     */
    public function upload()
    {
        if ($this->validate()) {
            $files = [];

            $filepath = $this->files_full_path;
            // прорубим путь, если папки нет
            // такое возможно, если к новому электронному документу приложен файл
            if (!is_dir($filepath) && !FileHelper::createDirectory($filepath)) return false;

            foreach ($this->initialFiles as $file) {
                // имя и полный путь к файлу полноразмерного изображения
                $fileAttached_fn = strtolower(Yii::$app->security->generateRandomString() . '.' . $file->extension);
                $fileAttached_ffp = $filepath . '/' . $fileAttached_fn;

                if ($file->saveAs($fileAttached_ffp)) {
                    // заполняем поля записи в базе о загруженном успешно файле
                    $fileAttachedmodel = new EdfFiles([
                        'ed_id' => $this->id,
                        'ffp' => $fileAttached_ffp,
                        'fn' => $fileAttached_fn,
                        'ofn' => $file->name,
                        'size' => filesize($fileAttached_ffp),
                    ]);

                    if ($fileAttachedmodel->save()) $files[] = $fileAttachedmodel->ffp;
                }
            }

            return $files;
        }

        return false;
    }

    /**
     * Рендерит необходимые кнопки для управления статусом электронного документа в зависимости от его статуса и роли пользователя.
     * @return mixed
     */
    public function renderSubmitButtons()
    {
        $canRoot = Yii::$app->user->can('root');
        $canManager = Yii::$app->user->can('sales_department_manager');
        $canOperatorHead = Yii::$app->user->can('operator_head');
        $canEdf = Yii::$app->user->can('edf');

        $siaButtons = [
            'create' => Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']),
            'save' => Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']),
            'history' => Html::a('<i class="fa fa-history"></i> История', ['#block-history'], ['class' => 'btn btn-default btn-lg', 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'block-history']),
            'create_order' => Html::submitButton('Отправить на формирование <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'create_order', 'title' => 'Отправить бухгалтера на формирование']),
            'approve_request' => Html::submitButton('Отправить на согласование <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'approve_request', 'title' => 'Отправить менеджеру на согласование']),
            'generate' => Html::a('Сформировать файлы', '#', ['class' => 'btn btn-default btn-lg', 'id' => 'btnGenerateFromTmplsForm']),
            'create_cp' => Html::submitButton('Создать пакет корреспонденции <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'create_cp']),
            'sign_wcp' => Html::submitButton('Подписать без пакета <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-default btn-lg', 'name' => 'sign_wcp', 'title' => 'Нажмите в случае, когда документ подписан руководством, но создавать пакет корреспонденции нет необходимости']),
            'reject' => Html::submitButton('<i class="fa fa-times" aria-hidden="true"></i> Отказ', ['class' => 'btn btn-danger btn-lg', 'name' => 'reject', 'title' => 'Отказать в согласовании (обязательно нужно будет указать причину согласования)']),
            'customer_reject' => Html::submitButton('<i class="fa fa-times" aria-hidden="true"></i> Отказ клиента', ['class' => 'btn btn-danger btn-lg', 'name' => 'customer_reject', 'title' => 'Заказчик отказался самостоятельно']),
            'approved' => Html::submitButton('Отдать на подпись', ['class' => 'btn btn-default btn-lg', 'name' => 'approved', 'title' => 'Документы можно отдавать на подпись руководству (заказчику)']),
            'rollback' => Html::submitButton('Вернуть', ['class' => 'btn btn-default btn-lg', 'name' => 'rollback', 'title' => 'Вернуть на согласование']),
        ];

        $result = '';

        if ($this->isNewRecord) {
            return $siaButtons['create'];
        }
        else {
            $result .= $siaButtons['history'] . ' ' . $siaButtons['save'] .
                ($canManager || ($this->state_id < EdfStates::STATE_ЗАЯВКА || $this->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА) ? '' : ' ' .$siaButtons['generate']);
        }

        switch ($this->state_id) {
            case EdfStates::STATE_ЧЕРНОВИК:
                $result .= ' ' . $siaButtons['create_order'];
                break;
            case EdfStates::STATE_ОТКАЗ:
                // возьмем последнюю запись в истории изменения статусов, чтобы выяснить, пользователей с какой ролью поместил документ в отказ
                $lastState = EdfStatesHistory::find()->where(['ed_id' => $this->id])->orderBy('`created_at` DESC')->one();

                if ($canOperatorHead) {
                    // старший оператор (он же делопроизводитель) может воспользоваться кнопкой "Отправить на согласование", если в отказ отправил менеджер
                    if ($lastState && $lastState->roleName == 'sales_department_manager') {
                        $result .= ' ' . $siaButtons['approve_request'];
                    }
                }

                if ($canManager || $canRoot) {
                    // менеджер может воспользоваться кнопкой "Отправить на формирование", если в отказ отправил делопроизводитель (старший оператор)
                    if ($lastState && ($lastState->roleName == 'edf' || $lastState->roleName == 'operator_head')) {
                        $result .= ' ' . $siaButtons['create_order'];
                    }
                }

                break;
            case EdfStates::STATE_ЗАЯВКА:
                if (!$canManager)
                $result .= ' ' . $siaButtons['approve_request'] .
                    ' ' . $siaButtons['reject'];
                break;
            case EdfStates::STATE_СОГЛАСОВАНИЕ:
                if ($canManager || $canRoot)
                // старшему оператору и делопроизводителю недоступны эти кнопки
                $result .= ' ' . $siaButtons['approved'] .
                    ' ' . Html::submitButton('На подписи у заказчика <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'customer_signs', 'title' => 'Отправить заказчику неподписанные с нашей стороны экземпляры']) .
                    ' ' . $siaButtons['reject'] . ' ' . $siaButtons['customer_reject'];
                break;
            case EdfStates::STATE_УТВЕРЖДЕНО:
                if ($canEdf || $canOperatorHead || $canRoot) {
                    // кнопки доступны только делопроизводителю
                    $result .= ' ' . Html::submitButton('Отдать на подпись <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'director_signs', 'title' => 'Отнести директору на подпись только с нашей стороны']) .
                        ' ' . $siaButtons['reject'];
                }

                $result .= ' ' . $siaButtons['rollback'];

                break;
            case EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА:
                if (empty($this->cp_id) && !$canManager)
                $result .= ' ' . $siaButtons['create_cp'] . ' ' . $siaButtons['sign_wcp'] . ' ' . $siaButtons['customer_reject'];
                break;
            case EdfStates::STATE_ПОДПИСАН_РУКОВОДСТВОМ:
            case EdfStates::STATE_НА_ПОДПИСИ_У_ЗАКАЗЧИКА:
            case EdfStates::STATE_ОТПРАВЛЕН:
            case EdfStates::STATE_ДОСТАВЛЕН:
                $result .=  ' ' . $siaButtons['customer_reject'];
                break;
            case EdfStates::STATE_ЗАВЕРШЕНО:
                break;
        }

        return $result;
    }

    /**
     * Делает выборку договоров и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $ca_id integer контрагент
     * @return array
     */
    public static function arrayMapOfContractsForSelect2($ca_id = null)
    {
        $query = self::find()->select([
            'id',
            'rep' => 'CONCAT("№ ", doc_num, " от ", DATE_FORMAT(doc_date, \'%d.%m.%Y\'), " г.")',
        ])->where(['type_id' => DocumentsTypes::TYPE_ДОГОВОР])->andWhere('state_id > ' . EdfStates::STATE_ЧЕРНОВИК);
        if (!empty($ca_id)) $query->andWhere(['fo_ca_id' => $ca_id]);

        return ArrayHelper::map($query->limit(50)->asArray()->all(), 'id', 'rep');
    }

    /**
     * Возвращает набор значений для поля "Тип дней отсрочки платежа".
     * @return array
     */
    public static function fetchDeferralTypes()
    {
        return [
            [
                'id' => self::DEFERRAL_TYPE_БАНКОВСКИЕ,
                'name' => 'Банковские',
            ],
            [
                'id' => self::DEFERRAL_TYPE_КАЛЕНДАРНЫЕ,
                'name' => 'Календарные',
            ],
        ];
    }

    /**
     * @return array
     */
    public static function fetchFilterTypical()
    {
        return [
            [
                'id' => self::FILTER_TYPICAL_ТИПОВЫЕ,
                'name' => 'Только типовые',
            ],
            [
                'id' => self::FILTER_TYPICAL_КАСТОМНЫЕ,
                'name' => 'Только кастомные',
            ],
        ];
    }

    /**
     * @return array
     */
    public static function fetchFilterScan()
    {
        return [
            [
                'id' => self::FILTER_SCAN_ЕСТЬ,
                'name' => 'Сканы в наличии',
            ],
            [
                'id' => self::FILTER_SCAN_НЕТ,
                'name' => 'Сканов пока нет',
            ],
        ];
    }

    /**
     * @return array
     */
    public static function fetchFilterOriginal()
    {
        return [
            [
                'id' => self::FILTER_ORIGINAL_ЕСТЬ,
                'name' => 'Оригиналы получены',
            ],
            [
                'id' => self::FILTER_ORIGINAL_НЕТ,
                'name' => 'Оригиналов нет',
            ],
        ];
    }

    /**
     * Делает выборку разновидностей дней для отсрочки платежа и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfDeferralTypesForSelect2()
    {
        return ArrayHelper::map(self::fetchDeferralTypes(), 'id', 'name');
    }

    /**
     * Делает выборку типов договоров и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfFilterTypicalForSelect2()
    {
        return ArrayHelper::map(self::fetchFilterTypical(), 'id', 'name');
    }

    /**
     * Делает выборку типов договоров и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfFilterScanForSelect2()
    {
        return ArrayHelper::map(self::fetchFilterScan(), 'id', 'name');
    }

    /**
     * Делает выборку типов договоров и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfFilterOriginalForSelect2()
    {
        return ArrayHelper::map(self::fetchFilterOriginal(), 'id', 'name');
    }

    /**
     * Возвращает в виде html-кода представление контрагента: его реквизиты, адрес.
     * @return string
     */
    public function getCounteragentHtmlRep()
    {
        $result = '';
        if (!empty($this->req_name_short)) $result .= '<strong>' . $this->req_name_short . '</strong>';
        if (!empty($this->req_inn)) $result .= ', <strong>ИНН</strong> ' . $this->req_inn;
        if (!empty($this->req_kpp)) $result .= ', <strong>КПП</strong> ' . $this->req_kpp;
        if (!empty($this->req_ogrn)) $result .= ', <strong>ОГРН</strong> ' . $this->req_ogrn;
        if (!empty($this->req_address_j)) $result .= ', <strong>юр. адрес </strong> ' . $this->req_address_j;
        if (!empty($this->req_dir_name)) $result .= '<p>' . (!empty($this->req_dir_post) ? $this->req_dir_post . ' ' : '') . $this->req_dir_name . '</p>';

        return Html::tag('div', $result, ['class' => 'form-group']);
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
    public function getCreatedByRoles()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'created_by'])->from([self::JOIN_CREATOR_ROLES_ALIAS => AuthAssignment::tableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'created_by'])->from([self::JOIN_CREATOR_PROFILE_ALIAS => Profile::tableName()]);
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
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(DocumentsTypes::class, ['id' => 'type_id']);
    }

    /**
     * Возвращает наименование типа документа.
     * @return string
     */
    public function getTypeName()
    {
        return !empty($this->type) ? $this->type->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Edf::class, ['id' => 'parent_id'])->from(['parentEdf' => Edf::tableName()]);
    }

    /**
     * Возвращает представление родительского документа.
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getParentRep()
    {
        return $this->parent ? '№ ' . $this->parent->doc_num . ' от ' . Yii::$app->formatter->asDate($this->parent->doc_date, 'php:d.m.Y') : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractType()
    {
        return $this->hasOne(ContractTypes::class, ['id' => 'ct_id']);
    }

    /**
     * Возвращает тип взаиморасчетов по договору.
     * @return string
     */
    public function getContractTypeName()
    {
        return !empty($this->contractType) ? $this->contractType->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(EdfStates::class, ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса, в котором пребывает электронный документ.
     * @return string
     */
    public function getStateName()
    {
        return !empty($this->state) ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'org_id']);
    }

    /**
     * Возвращает наименование организации.
     * @return string
     */
    public function getOrganizationName()
    {
        return !empty($this->organization) ? $this->organization->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBankAccount()
    {
        return $this->hasOne(OrganizationsBas::class, ['id' => 'ba_id']);
    }

    /**
     * Возвращает номер счета контрагента.
     * @return string
     */
    public function getBankAccountNumber()
    {
        return $this->bankAccount ? $this->bankAccount->bank_an : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
    }

    /**
     * Возвращает E-mail менеджера.
     * @return string
     */
    public function getManagerEmail()
    {
        return !empty($this->manager) ? $this->manager->email : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagerProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'manager_id'])->from([self::JOIN_MANAGER_PROFILE_ALIAS => Profile::tableName()]);
    }

    /**
     * Возвращает имя ответственного за документ.
     * @return string
     */
    public function getManagerProfileName()
    {
        return !empty($this->managerProfile) ? (!empty($this->managerProfile->name) ? $this->managerProfile->name : $this->manager->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagerRoles()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'manager_id'])->from([self::JOIN_MANAGER_ROLES_ALIAS => AuthAssignment::tableName()]);
    }

    /*
     * @return \yii\db\ActiveQuery
    */
    public function getCp()
    {
        return $this->hasOne(CorrespondencePackages::class, ['id' => 'cp_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTablePart()
    {
        return $this->hasMany(EdfTp::class, ['ed_id' => 'id']);
    }

    /*
    * @return \yii\db\ActiveQuery
    */
    public function getEdfFiles()
    {
        return $this->hasMany(EdfFiles::class, ['ed_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdfStatesHistories()
    {
        return $this->hasMany(EdfStatesHistory::class, ['ed_id' => 'id']);
    }
}
