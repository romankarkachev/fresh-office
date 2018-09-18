<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ce_addresses".
 *
 * @property integer $id
 * @property integer $message_id
 * @property string $type
 * @property string $email
 * @property string $name
 *
 * @property CeMessages $message
 */
class CEAddresses extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ce_addresses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id', 'type', 'email'], 'required'],
            [['message_id'], 'integer'],
            [['type'], 'string', 'max' => 10],
            [['email', 'name'], 'string', 'max' => 255],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => CeMessages::className(), 'targetAttribute' => ['message_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => 'Письмо',
            'type' => 'Тип',
            'email' => 'Адрес',
            'name' => 'Имя отправителя',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage()
    {
        return $this->hasOne(CeMessages::className(), ['id' => 'message_id']);
    }
}
