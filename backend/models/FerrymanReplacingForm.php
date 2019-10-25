<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\Drivers;
use common\models\Ferrymen;

/**
 * @property integer $driver_id
 * @property integer $ferryman_id
 *
 * @property Drivers $driver
 * @property Ferrymen $ferryman
 */
class FerrymanReplacingForm extends Model
{
    /**
     * @var integer водитель
     */
    public $driver_id;

    /**
     * @var integer перевозчик
     */
    public $ferryman_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['driver_id', 'ferryman_id'], 'required'],
            [['driver_id'], 'exist', 'skipOnError' => true, 'targetClass' => Drivers::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'driver_id' => 'Водитель',
            'ferryman_id' => 'Перевозчик',
        ];
    }

    /**
     * Выполняет замену перевозчика на нового.
     * @return bool
     */
    public function replaceFerryman()
    {
        return $this->getDriver()->updateAttributes([
            'ferryman_id' => $this->ferryman_id,
        ]) > 0;
    }

    /**
     * @return Drivers
     */
    public function getDriver()
    {
        return Drivers::findOne($this->driver_id);
    }

    /**
     * @return Ferrymen
     */
    public function getFerrymen()
    {
        return Ferrymen::findOne($this->ferryman_id);
    }
}
