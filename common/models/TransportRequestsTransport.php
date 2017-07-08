<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "transport_requests_transport".
 *
 * @property integer $id
 * @property integer $tr_id
 * @property integer $tt_id
 * @property string $amount
 *
 * @property string $ttName
 *
 * @property TransportTypes $tt
 * @property TransportRequests $tr
 */
class TransportRequestsTransport extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport_requests_transport';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tr_id', 'tt_id'], 'required'],
            [['tr_id', 'tt_id'], 'integer'],
            [['amount'], 'number'],
            [['tt_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportTypes::className(), 'targetAttribute' => ['tt_id' => 'id']],
            [['tr_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportRequests::className(), 'targetAttribute' => ['tr_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tr_id' => 'Запрос на транспорт',
            'tt_id' => 'Тип техники',
            'amount' => 'Стоимость',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTr()
    {
        return $this->hasOne(TransportRequests::className(), ['id' => 'tr_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTt()
    {
        return $this->hasOne(TransportTypes::className(), ['id' => 'tt_id']);
    }

    /**
     * Возвращает наименование типа техники.
     * @return string
     */
    public function getTtName()
    {
        return $this->tt != null ? $this->tt->name : '';
    }
}
