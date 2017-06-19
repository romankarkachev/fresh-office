<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "drivers_instructings".
 *
 * @property integer $id
 * @property integer $driver_id
 * @property string $instructed_at
 * @property string $place
 * @property string $responsible
 *
 * @property Drivers $driver
 */
class DriversInstructings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'drivers_instructings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['driver_id', 'instructed_at'], 'required'],
            [['driver_id'], 'integer'],
            [['instructed_at'], 'safe'],
            [['place', 'responsible'], 'string', 'max' => 50],
            [['driver_id'], 'exist', 'skipOnError' => true, 'targetClass' => Drivers::className(), 'targetAttribute' => ['driver_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'driver_id' => 'Водитель',
            'instructed_at' => 'Дата проведения',
            'place' => 'Место проведения',
            'responsible' => 'Ответственный',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(Drivers::className(), ['id' => 'driver_id']);
    }
}
