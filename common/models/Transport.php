<?php

namespace common\models;

use Yii;
use common\behaviors\IndexFieldBehavior;

/**
 * This is the model class for table "transport".
 *
 * @property integer $id
 * @property integer $ferryman_id
 * @property integer $tt_id
 * @property string $vin
 * @property string $vin_index
 * @property string $rn
 * @property string $rn_index
 * @property string $trailer_rn
 * @property integer $tc_id
 * @property string $comment
 *
 * @property TechnicalConditions $tc
 * @property Ferrymen $ferryman
 * @property TransportTypes $tt
 * @property TransportInspections[] $transportInspections
 */
class Transport extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id'], 'required'],
            [['ferryman_id', 'tt_id', 'tc_id'], 'integer'],
            [['comment'], 'string'],
            [['vin', 'vin_index'], 'string', 'max' => 50],
            [['rn', 'rn_index', 'trailer_rn'], 'string', 'max' => 30],
            [['tc_id'], 'exist', 'skipOnError' => true, 'targetClass' => TechnicalConditions::className(), 'targetAttribute' => ['tc_id' => 'id']],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
            [['tt_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportTypes::className(), 'targetAttribute' => ['tt_id' => 'id']],
            // собственные правила валидации
            ['vin', 'validateVin'],
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
            'tt_id' => 'Тип',
            'vin' => 'VIN',
            'rn' => 'Госномер',
            'trailer_rn' => 'Прицеп',
            'tc_id' => 'Техническое состояние',
            'comment' => 'Примечание',
            // для сортировки
            'ttName' => 'Тип',
            'tcName' => 'Техническое состояние',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'indexVinField' => [
                'class' => 'common\behaviors\IndexFieldBehavior',
                'in_attribute' => 'vin',
                'out_attribute' => 'vin_index',
            ],
            'indexRnField' => [
                'class' => 'common\behaviors\IndexFieldBehavior',
                'in_attribute' => 'rn',
                'out_attribute' => 'rn_index',
            ]
        ];
    }

    /**
     * Собственное правило валидации для номера водительского удостоверения
     */
    public function validateVin()
    {
        if (self::find()->where(['vin_index' => IndexFieldBehavior::processValue($this->vin)])->count() > 0)
            $this->addError('vin', 'Автомобиль с таким VIN уже существует.');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::className(), ['id' => 'ferryman_id']);
    }

    /**
     * Возвращает наименование перевозчка.
     * @return string
     */
    public function getFerrymanName()
    {
        return $this->ferryman != null ? $this->ferryman->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTt()
    {
        return $this->hasOne(TransportTypes::className(), ['id' => 'tt_id']);
    }

    /**
     * Возвращает наименование типа транспортного средства.
     * @return string
     */
    public function getTtName()
    {
        return $this->tt != null ? $this->tt->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTc()
    {
        return $this->hasOne(TechnicalConditions::className(), ['id' => 'tc_id']);
    }

    /**
     * Возвращает наименование технического состояния транспортного средства.
     * @return string
     */
    public function getTcName()
    {
        return $this->tc != null ? $this->tc->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportInspections()
    {
        return $this->hasMany(TransportInspections::className(), ['transport_id' => 'id']);
    }
}
