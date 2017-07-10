<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "transport_by_cities_cost".
 *
 * @property integer $id
 * @property integer $city_id
 * @property integer $tt_id
 * @property string $amount
 *
 * @property TransportTypes $tt
 * @property Cities $city
 */
class TransportByCitiesCost extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport_by_cities_cost';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['city_id', 'tt_id'], 'required'],
            [['city_id', 'tt_id'], 'integer'],
            [['amount'], 'number'],
            [['tt_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportTypes::className(), 'targetAttribute' => ['tt_id' => 'id']],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cities::className(), 'targetAttribute' => ['city_id' => 'city_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'city_id' => 'Город',
            'tt_id' => 'Тип техники',
            'amount' => 'Стоимость',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTt()
    {
        return $this->hasOne(TransportTypes::className(), ['id' => 'tt_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(Cities::className(), ['city_id' => 'city_id']);
    }
}
