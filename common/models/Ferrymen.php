<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ferrymen".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property integer $fo_id
 * @property string $name
 * @property string $name_crm
 * @property string $name_full
 * @property string $name_short
 * @property string $inn
 * @property string $kpp
 * @property string $ogrn
 * @property string $address_j
 * @property string $address_f
 * @property integer $opfh_id
 * @property integer $tax_kind
 * @property integer $ft_id
 * @property integer $pc_id
 * @property integer $state_id
 * @property string $phone
 * @property string $email
 * @property string $contact_person
 * @property string $post
 * @property string $phone_dir
 * @property string $email_dir
 * @property string $contact_person_dir
 * @property string $post_dir
 * @property string $ati_code
 * @property string $contract_expires_at
 * @property integer $notify_when_payment_orders_created
 * @property integer $user_id
 * @property integer $ppdq количество дней постоплаты
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property Opfh $opfh
 * @property FerrymenTypes $ft
 * @property PaymentConditions $pc
 * @property User $user
 * @property Drivers[] $drivers
 * @property Projects[] $projects
 * @property Transport[] $transport
 * @property FerrymenFiles[] $ferrymenFiles
 * @property FerrymenBankCards[] $ferrymenBankCards
 * @property FerrymenBankDetails[] $ferrymenBankDetails
 * @property PaymentOrders[] $paymentOrders
 * @property FerrymenInvitations[] $ferrymenInvitations
 */
class Ferrymen extends \yii\db\ActiveRecord
{
    /**
     * Вычисляемое виртуальное поле.
     * @var integer количество водителей у перевозчика
     */
    public $driversCount;

    /**
     * Вычисляемое виртуальное поле.
     * @var integer перечень водителей перевозчика в строку через запятую
     */
    public $driversDetails;

    /**
     * Вычисляемое виртуальное поле.
     * @var integer количество транспортных средств у перевозчика
     */
    public $transportCount;

    /**
     * Вычисляемое виртуальное поле.
     * @var integer перечень транспортных средств перевозчика в строку через запятую
     */
    public $transportDetails;

    /**
     * Статусы перевозчиков, водителей, транспорта
     */
    const STATE_НЕТ_НАРЕКАНИЙ = 1;
    const STATE_ЕСТЬ_ЗАМЕЧАНИЯ = 2;
    const STATE_ЧЕРНЫЙ_СПИСОК = 3;

    /**
     * Статусы плательщиков НДС
     */
    const TAX_KIND_НЕПЛАТЕЛЬЩИК = 0;
    const TAX_KIND_ПЛАТЕЛЬЩИК = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ferrymen';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'ft_id', 'pc_id'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by', 'fo_id', 'opfh_id', 'tax_kind', 'ft_id', 'pc_id', 'state_id', 'notify_when_payment_orders_created', 'user_id', 'ppdq'], 'integer'],
            [['name_full', 'name_short', 'address_j', 'address_f'], 'string'],
            [['contract_expires_at'], 'safe'],
            [['name', 'name_crm', 'email', 'email_dir'], 'string', 'max' => 255],
            [['inn'], 'string', 'min' => 10, 'max' => 12],
            [['kpp'], 'string', 'length' => 9],
            [['ogrn'], 'string', 'max' => 15],
            [['phone', 'contact_person', 'phone_dir', 'contact_person_dir'], 'string', 'max' => 50],
            [['post', 'post_dir'], 'string', 'max' => 100],
            [['ati_code'], 'string', 'max' => 9],
            [['user_id'], 'unique'],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['opfh_id'], 'exist', 'skipOnError' => true, 'targetClass' => Opfh::className(), 'targetAttribute' => ['opfh_id' => 'id']],
            [['pc_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentConditions::className(), 'targetAttribute' => ['pc_id' => 'id']],
            [['ft_id'], 'exist', 'skipOnError' => true, 'targetClass' => FerrymenTypes::className(), 'targetAttribute' => ['ft_id' => 'id']],
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
            'updated_at' => 'Дата и время изменения',
            'updated_by' => 'Автор изменений',
            'fo_id' => 'Идентификатор в Fresh Office',
            'name' => 'Наименование',
            'name_crm' => 'Наименование в CRM',
            'name_full' => 'Полное наименование',
            'name_short' => 'Сокращенное наименование наименование',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН(ИП)',
            'address_j' => 'Адрес юридический',
            'address_f' => 'Адрес фактический',
            'opfh_id' => 'ОПФХ',
            'tax_kind' => 'Плательщик НДС', // 0 - нет, 1 - да
            'ft_id' => 'Тип',
            'pc_id' => 'Условия оплаты',
            'state_id' => 'Статус', // 1 - нареканий нет, 2 - есть замечания, 3 - черный список
            'phone' => 'Телефоны',
            'email' => 'E-mail',
            'contact_person' => 'Имя',
            'post' => 'Должность',
            'phone_dir' => 'Телефоны',
            'email_dir' => 'E-mail',
            'contact_person_dir' => 'Имя',
            'post_dir' => 'Должность',
            'ati_code' => 'Код АТИ',
            'contract_expires_at' => 'Срок действия договора',
            'notify_when_payment_orders_created' => 'Необходимость отправлять уведомление перевозчику при импорте платежного ордера на него',
            'user_id' => 'Пользователь системы',
            'ppdq' => 'Количество дней постоплаты',
            // для вычисляемых полей
            'ftName' => 'Тип',
            'pcName' => 'Условия оплаты',
            'stateName' => 'Статус',
            'driversCount' => 'Водителей',
            'transportCount' => 'Автомобилей',
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
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_by'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением документа

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = FerrymenFiles::find()->where(['ferryman_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            // удаляем водителей
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $drivers = Drivers::find()->where(['ferryman_id' => $this->id])->all();
            foreach ($drivers as $driver) $driver->delete();

            // удаляем транспорт
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $transports = Transport::find()->where(['ferryman_id' => $this->id])->all();
            foreach ($transports as $transport) $transport->delete();

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        if (isset($changedAttributes['state_id'])) {
            Drivers::updateAll([
                'state_id' => $this->state_id,
            ], [
                'ferryman_id' => $this->id,
            ]);

            Transport::updateAll([
                'state_id' => $this->state_id,
            ], [
                'ferryman_id' => $this->id,
            ]);
        }

        return true;
    }

    /**
     * Форматирует номер телефона, переданный в параметрах как число и возвращает в виде +7 (ххх) ххх-хх-хх.
     * @param $phone string
     * @return string
     */
    public static function normalizePhone($phone)
    {
        $result = '<нет номера телефона>';
        if ($phone != null && $phone != '')
            if (preg_match('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches)) {
                $result = '+7 (' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3] . '-' . $matches[4];
            }
            else
                // не удалось преобразовать в человеческий вид - отображаем как есть
                $result = $phone;
        return $result;
    }

    /**
     * Возвращает в виде массива разновидности статусов перевозчиков, водителей, транспорта.
     * @return array
     */
    public static function fetchStates()
    {
        return [
            [
                'id' => self::STATE_НЕТ_НАРЕКАНИЙ,
                'name' => 'Нареканий нет',
            ],
            [
                'id' => self::STATE_ЕСТЬ_ЗАМЕЧАНИЯ,
                'name' => 'Есть замечания',
            ],
            [
                'id' => self::STATE_ЧЕРНЫЙ_СПИСОК,
                'name' => 'Черный список',
            ],
        ];
    }

    /**
     * Возвращает в виде массива разновидности плательщиков НДС.
     * @return array
     */
    public static function fetchTaxKinds()
    {
        return [
            [
                'id' => self::TAX_KIND_НЕПЛАТЕЛЬЩИК,
                'name' => 'Неплательщик',
            ],
            [
                'id' => self::TAX_KIND_ПЛАТЕЛЬЩИК,
                'name' => 'Плательщик',
            ],
        ];
    }

    /**
     * Массив разновидностей видов файлов к водителям и описания к ним.
     */
    public static function fetchAttachedToDriversFilesDescriptions()
    {
        return [
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ЛИЦЕВАЯ,
                'title' => 'Водительское удостоверение',
                'hint' => 'Лицевая сторона',
            ],
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ОБОРОТ,
                'title' => 'Водительское удостоверение',
                'hint' => 'Оборотная сторона',
            ],
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ГЛАВНАЯ,
                'title' => 'Паспорт',
                'hint' => 'Главный разворот',
            ],
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ПРОПИСКА,
                'title' => 'Паспорт',
                'hint' => 'Прописка',
            ],
        ];
    }

    /**
     * Массив разновидностей файлов к транспорту и описания к ним.
     */
    public static function fetchAttachedToTransportFilesDescriptions()
    {
        return [
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_ОСАГО,
                'title' => 'ОСАГО',
                'hint' => 'Лицевая сторона',
            ],
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ЛИЦЕВАЯ,
                'title' => 'ПТС',
                'hint' => 'Лицевая сторона',
            ],
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ОБОРОТ,
                'title' => 'ПТС',
                'hint' => 'Оборотная сторона',
            ],
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ЛИЦЕВАЯ,
                'title' => 'СТС',
                'hint' => 'Лицевая сторона',
            ],
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ОБОРОТ,
                'title' => 'СТС',
                'hint' => 'Оборотная сторона',
            ],
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_ДИАГНОСТИЧЕСКАЯ_КАРТА,
                'title' => 'Диагностическая карта',
                'hint' => 'Лицевая сторона',
            ],
            [
                'id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_ФОТО_АВТОМОБИЛЯ,
                'title' => 'Фото автомобиля',
                'hint' => 'Любой вид',
            ],
        ];
    }

    /**
     * Делает выборку статусов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfStatesForSelect2()
    {
        return ArrayHelper::map(self::fetchStates(), 'id', 'name');
    }

    /**
     * Делает выборку плательщиков НДС и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfTaxKindsForSelect2()
    {
        return ArrayHelper::map(self::fetchTaxKinds(), 'id', 'name');
    }

    /**
     * Делает выборку перевозчиков и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * Делает выборку перевозчиков и возвращает в виде массива, в котором поиск удобно осуществлять по наименованию.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSearchByCrmName()
    {
        return ArrayHelper::map(self::find()->all(), 'name_crm', 'id');
    }

    /**
     * Делает выборку водителей и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public function arrayMapOfDriversForSelect2()
    {
        return ArrayHelper::map(Drivers::find()->select(['id', 'name' => 'CONCAT(surname, " ", name, " ", patronymic)'])->where(['ferryman_id' => $this->id, 'is_deleted' => false])->all(), 'id', 'name');
    }

    /**
     * Делает выборку автомобилей и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public function arrayMapOfTransportForSelect2()
    {
        return ArrayHelper::map(Transport::find()->where(['ferryman_id' => $this->id, 'is_deleted' => false])->all(), 'id', 'representation');
    }

    /**
     * Возвращает заголовок разновидности приаттаченных файлов. Применяется как для водителей, так и для транспорта.
     * @param $sourceTable array массив, по которому будет осуществляться поиск
     * @param $id integer идентификатор, по которому осуществляется поиск
     * @param $field string поле, значение которого будет возвращено
     * @return string
     */
    public static function getAfd($sourceTable, $id, $field)
    {
        $key = array_search($id, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key][$field];

        return '';
    }

    /**
     * Возвращает наименование статуса.
     * @return string
     */
    public function getStateName()
    {
        if (null === $this->state_id) {
            return '<не определен>';
        }

        $sourceTable = self::fetchStates();
        $key = array_search($this->state_id, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }

    /**
     * Возвращает наименование статуса.
     * @param $state_id integer
     * @return string
     */
    public static function getIndepStateName($state_id)
    {
        if ($state_id != null) {
            $sourceTable = self::fetchStates();
            $key = array_search($state_id, array_column($sourceTable, 'id'));
            if (false !== $key) return $sourceTable[$key]['name'];
        }

        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOpfh()
    {
        return $this->hasOne(Opfh::className(), ['id' => 'opfh_id']);
    }

    /**
     * Возвращает наименование организационно-правовой формы хозяйствования.
     * @return string
     */
    public function getOpfhName()
    {
        return $this->opfh != null ? $this->opfh->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFt()
    {
        return $this->hasOne(FerrymenTypes::className(), ['id' => 'ft_id']);
    }

    /**
     * Возвращает наименование типа перевозчика.
     * @return string
     */
    public function getFtName()
    {
        return $this->ft != null ? $this->ft->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPc()
    {
        return $this->hasOne(PaymentConditions::className(), ['id' => 'pc_id']);
    }

    /**
     * Возвращает наименование условия оплаты.
     * @return string
     */
    public function getPcName()
    {
        return $this->pc != null ? $this->pc->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDrivers()
    {
        return $this->hasMany(Drivers::className(), ['ferryman_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransport()
    {
        return $this->hasMany(Transport::className(), ['ferryman_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerrymenFiles()
    {
        return $this->hasMany(FerrymenFiles::className(), ['ferryman_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerrymenBankCards()
    {
        return $this->hasMany(FerrymenBankCards::className(), ['ferryman_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerrymenBankDetails()
    {
        return $this->hasMany(FerrymenBankDetails::className(), ['ferryman_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentOrders()
    {
        return $this->hasMany(PaymentOrders::className(), ['ferryman_id' => 'id']);
    }
}
