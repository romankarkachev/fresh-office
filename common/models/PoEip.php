<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "po_eip".
 *
 * @property int $id
 * @property int $ei_id Статья расходов
 * @property int $property_id Свойство
 * @property int $is_required Является ли обязательным для заполнения
 *
 * @property PoProperties $property
 * @property PoEi $ei
 */
class PoEip extends \yii\db\ActiveRecord
{
    /**
     * Идентификаторы элементов страницы
     */
    const DOM_IDS = [
        // форма для интерактивного добавления значения свойства
        'PJAX_FORM_ID' => 'frmNewEP',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po_eip';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ei_id', 'property_id'], 'required'],
            [['ei_id', 'property_id', 'is_required'], 'integer'],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoProperties::class, 'targetAttribute' => ['property_id' => 'id']],
            [['ei_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoEi::class, 'targetAttribute' => ['ei_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ei_id' => 'Статья расходов',
            'property_id' => 'Свойство',
            'is_required' => 'Является ли обязательным для заполнения',
            // вычисляемые поля
            'eiName' => 'Статья расходов',
            'propertyName' => 'Свойство',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEi()
    {
        return $this->hasOne(PoEi::class, ['id' => 'ei_id']);
    }

    /**
     * Возвращает наименование статьи.
     * @return string
     */
    public function getEiName()
    {
        return !empty($this->ei) ? $this->ei->name : '';
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
}
