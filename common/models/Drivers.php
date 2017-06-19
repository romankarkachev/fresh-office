<?php

namespace common\models;

use Yii;
use common\behaviors\IndexFieldBehavior;

/**
 * This is the model class for table "drivers".
 *
 * @property integer $id
 * @property integer $ferryman_id
 * @property string $surname
 * @property string $name
 * @property string $patronymic
 * @property string $driver_license
 * @property string $driver_license_index
 * @property string $phone
 *
 * @property Ferrymen $ferryman
 * @property DriversInstructings[] $driversInstructings
 */
class Drivers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'drivers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id', 'surname', 'name', 'driver_license', 'phone'], 'required'],
            [['ferryman_id'], 'integer'],
            [['surname', 'name', 'patronymic'], 'string', 'max' => 50],
            [['driver_license', 'driver_license_index'], 'string', 'max' => 30],
            [['phone'], 'string', 'min' => 15],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
            // собственные правила валидации
            ['phone', 'validatePhone'],
            ['driver_license', 'validateDriverLicense'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ferryman_id' => 'Перевозчик',
            'surname' => 'Фамилия',
            'name' => 'Имя',
            'patronymic' => 'Отчество',
            'driver_license' => 'Водительское удостоверение',
            'phone' => 'Телефон',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'indexField' => [
                'class' => 'common\behaviors\IndexFieldBehavior',
                'in_attribute' => 'driver_license',
                'out_attribute' => 'driver_license_index',
            ]
        ];
    }

    /**
     * Собственное правило валидации для номера телефона.
     */
    public function validatePhone()
    {
        $phone_processed = preg_replace("/[^0-9]/", '', $this->phone);
        if (strlen($phone_processed) < 10)
            $this->addError('phone', 'Номер телефона должен состоять из 10 цифр.');
    }

    /**
     * Собственное правило валидации для номера водительского удостоверения
     */
    public function validateDriverLicense()
    {
        if (self::find()->where(['driver_license_index' => IndexFieldBehavior::processValue($this->driver_license)])->count() > 0)
            $this->addError('driver_license', 'Водитель с таким удостоверением уже существует.');
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                // убираем из номера телефона все символы кроме цифр
                $this->phone = preg_replace("/[^0-9]/", '', $this->phone);
            }

            return true;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::className(), ['id' => 'ferryman_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDriversInstructings()
    {
        return $this->hasMany(DriversInstructings::className(), ['driver_id' => 'id']);
    }
}
