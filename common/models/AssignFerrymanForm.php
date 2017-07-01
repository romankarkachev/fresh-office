<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * @property string $project_ids
 * @property integer $ferryman_id
 * @property integer $driver_id
 * @property integer $transport_id
 *
 * @property Ferrymen $ferryman
 * @property Drivers $driver
 * @property Transport $transport
 */
class AssignFerrymanForm extends Model
{
    /**
     * Идентификаторы проектов.
     * @var string
     */
    public $project_ids;

    /**
     * Идентификатор перевозчика.
     * @var integer
     */
    public $ferryman_id;

    /**
     * Идентификатор водителя.
     * @var integer
     */
    public $driver_id;

    /**
     * Идентификатор автомобиля.
     * @var integer
     */
    public $transport_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_ids', 'ferryman_id', 'driver_id', 'transport_id'], 'required'],
            [['ferryman_id', 'driver_id', 'transport_id'], 'integer'],
            [['project_ids'], 'safe'],
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
            'project_ids' => 'Проекты',
            'ferryman_id' => 'Перевозчик',
            'driver_id' => 'Водитель',
            'transport_id' => 'Транспорт',
        ];
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
