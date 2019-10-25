<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "po_pop".
 *
 * @property int $id
 * @property int $po_id Платежный ордер
 * @property int $ei_id Статья расходов
 * @property int $property_id Свойство
 * @property int $value_id Значение свойства
 *
 * @property PoEi $ei
 * @property Po $po
 * @property PoProperties $property
 * @property PoValues $value
 */
class PoPop extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po_pop';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['po_id', 'ei_id', 'property_id', 'value_id'], 'required'],
            [['po_id', 'ei_id', 'property_id', 'value_id'], 'integer'],
            [['ei_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoEi::class, 'targetAttribute' => ['ei_id' => 'id']],
            [['po_id'], 'exist', 'skipOnError' => true, 'targetClass' => Po::class, 'targetAttribute' => ['po_id' => 'id']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoProperties::class, 'targetAttribute' => ['property_id' => 'id']],
            [['value_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoValues::class, 'targetAttribute' => ['value_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'po_id' => 'Платежный ордер',
            'ei_id' => 'Статья расходов',
            'property_id' => 'Свойство',
            'value_id' => 'Значение свойства',
            // вычисляемые поля
            'poRep' => 'Платежный ордер',
            'eiName' => 'Статья расходов',
            'propertyName' => 'Свойство',
            'valueName' => 'Значение свойства',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPo()
    {
        return $this->hasOne(Po::class, ['id' => 'po_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEi()
    {
        return $this->hasOne(PoEi::class, ['id' => 'ei_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProperty()
    {
        return $this->hasOne(PoProperties::class, ['id' => 'property_id']);
    }

    /**
     * Возвращает наименование свойства.
     * @return string
     */
    public function getPropertyName()
    {
        return !empty($this->property) ? $this->property->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getValue()
    {
        return $this->hasOne(PoValues::class, ['id' => 'value_id']);
    }

    /**
     * Возвращает наименование значения свойства.
     * @return string
     */
    public function getValueName()
    {
        return !empty($this->value) ? $this->value->name : '';
    }
}
