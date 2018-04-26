<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "transport_load_types".
 *
 * @property integer $id
 * @property integer $transport_id
 * @property integer $lt_id
 *
 * @property LoadTypes $lt
 * @property Transport $transport
 */
class TransportLoadTypes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport_load_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transport_id', 'lt_id'], 'required'],
            [['transport_id', 'lt_id'], 'integer'],
            [['lt_id'], 'exist', 'skipOnError' => true, 'targetClass' => LoadTypes::className(), 'targetAttribute' => ['lt_id' => 'id']],
            [['transport_id'], 'exist', 'skipOnError' => true, 'targetClass' => Transport::className(), 'targetAttribute' => ['transport_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transport_id' => 'Транспортное средство',
            'lt_id' => 'Тип погрузки',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLt()
    {
        return $this->hasOne(LoadTypes::className(), ['id' => 'lt_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransport()
    {
        return $this->hasOne(Transport::className(), ['id' => 'transport_id']);
    }
}
