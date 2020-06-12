<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ferrymen_prices".
 *
 * @property int $id
 * @property int $ferryman_id Перевозчик
 * @property string $price Стоимость
 * @property string $cost Себестоимость
 *
 * @property string $ferrymanName
 *
 * @property Ferrymen $ferryman
 */
class FerrymenPrices extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ferrymen_prices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ferryman_id'], 'required'],
            [['ferryman_id'], 'integer'],
            ['ferryman_id', 'unique', 'message' => 'По данному перевозчику цена уже введена.'],
            [['price', 'cost'], 'number'],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::class, 'targetAttribute' => ['ferryman_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ferryman_id' => 'Перевозчик',
            'price' => 'Стоимость',
            'cost' => 'Себестоимость',
            // вычисляемые поля
            'ferrymanName' => 'Перевозчик',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::class, ['id' => 'ferryman_id']);
    }

    /**
     * Возвращает наименование перевозчика.
     * @return string
     */
    public function getFerrymanName()
    {
        return !empty($this->ferryman) ? $this->ferryman->name : '';
    }
}
