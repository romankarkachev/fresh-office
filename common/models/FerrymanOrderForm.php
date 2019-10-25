<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * @property string $project_id
 * @property integer $org_id
 * @property integer $ferryman_id
 * @property integer $driver_id
 * @property integer $transport_id
 *
 * @property Organizations $organization
 * @property Ferrymen $ferryman
 * @property Drivers $driver
 * @property Transport $transport
 */
class FerrymanOrderForm extends Model
{
    /**
     * Значение по-умолчанию для поля "Время загрузки"
     */
    const DEFAULT_VALUE_LOAD_TIME = '09:00';

    /**
     * Значение по-умолчанию для поля "Особые условия"
     */
    const DEFAULT_VALUE_SPECIAL_CONDITIONS = 'На погрузке и выгрузке водитель должен быть в закрытой одежде и закрытой обуви (в случае несоблюдения данного пункта - штраф 2000 (две тысячи) руб.). Машина должна  быть опломбирована после погрузки. На выгрузке не будет печати, только подпись ответственного лица. Водитель обязан следить за погрузкой паллет в один ярус, в случае отклонения сообщить контактному лицу.';

    /**
     * Значение по-умолчанию для поля "Выгрузка"
     */
    const DEFAULT_VALUE_UNLOAD_ADDRESS = 'г. Воскресенск, ул. Московская, АЗС АИСТ №1 перед заездом на территорию позвонить контактному лицу отдела логистики и без его разрешения на территорию не заезжать';

    /**
     * Значения для поля "НДС"
     */
    const VAT_MIT = 1;
    const VAT_OHNE = 0;

    /**
     * @var string идентификатор проекта
     */
    public $project_id;

    /**
     * @var integer идентификатор организации
     */
    public $org_id;

    /**
     * @var integer идентификатор перевозчика
     */
    public $ferryman_id;

    /**
     * @var integer идентификатор водителя
     */
    public $driver_id;

    /**
     * @var integer идентификатор автомобиля
     */
    public $transport_id;

    /**
     * @var string адрес выгрузки
     */
    public $unload_address;

    /**
     * @var string время загрузки
     */
    public $load_time;

    /**
     * @var double сумма (из поля проекта "Себестоимость")
     */
    public $amount;

    /**
     * @var bool признак уплаты НДС (0 - нет, 1 - да)
     */
    public $hasVat;

    /**
     * @var string дата выгрузки
     */
    public $special_conditions;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'org_id', 'ferryman_id', 'driver_id', 'transport_id', 'unload_address', 'load_time'], 'required'],
            [['project_id', 'org_id', 'ferryman_id', 'driver_id', 'transport_id', 'hasVat'], 'integer'],
            [['amount'], 'number'],
            [['unload_address', 'load_time', 'special_conditions'], 'string'],
            [['special_conditions'], 'default', 'value'=> self::DEFAULT_VALUE_SPECIAL_CONDITIONS],
            [['load_time'], 'default', 'value'=> self::DEFAULT_VALUE_LOAD_TIME],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
            [['driver_id'], 'exist', 'skipOnError' => true, 'targetClass' => Drivers::className(), 'targetAttribute' => ['driver_id' => 'id']],
            [['transport_id'], 'exist', 'skipOnError' => true, 'targetClass' => Transport::className(), 'targetAttribute' => ['transport_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'project_id' => 'Проект',
            'org_id' => 'Заказчик (наша организация)',
            'ferryman_id' => 'Перевозчик',
            'driver_id' => 'Водитель',
            'transport_id' => 'Транспорт',
            'unload_address' => 'Адрес выгрузки',
            'load_time' => 'Время загрузки',
            'amount' => 'Сумма',
            'hasVat' => 'НДС',
            'special_conditions' => 'Особые условия',
        ];
    }

    /**
     * Возвращает массив со значениями для поля "НДС".
     * @return array
     */
    public static function fetchVatKinds()
    {
        return [
            [
                'id' => self::VAT_MIT,
                'name' => 'С НДС',
            ],
            [
                'id' => self::VAT_OHNE,
                'name' => 'Без НДС',
                'hint' => 'Платеж без НДС',
            ],
        ];
    }

    /**
     * @return Organizations
     */
    public function getOrganization()
    {
        return Organizations::findOne(['id' => $this->org_id]);
    }

    /**
     * @return Ferrymen
     */
    public function getFerryman()
    {
        return Ferrymen::findOne(['id' => $this->ferryman_id]);
    }

    /**
     * @return Drivers
     */
    public function getDriver()
    {
        return Drivers::findOne(['id' => $this->driver_id]);
    }

    /**
     * @return Transport
     */
    public function getTransport()
    {
        return Transport::findOne(['id' => $this->transport_id]);
    }
}
