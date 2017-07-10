<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use dektrium\user\models\Profile;

/**
 * This is the model class for table "transport_requests_dialogs".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $tr_id
 * @property string $message
 *
 * @property string $createdByName
 *
 * @property TransportRequests $tr
 * @property User $createdBy
 */
class TransportRequestsDialogs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport_requests_dialogs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tr_id', 'message'], 'required'],
            [['created_at', 'created_by', 'tr_id'], 'integer'],
            [['message'], 'string'],
            [['tr_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportRequests::className(), 'targetAttribute' => ['tr_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'tr_id' => 'Запрос на транспорт',
            'message' => 'Текст сообщения',
            // для вычисляемых полей
            'createdByName' => 'Автор',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by']);
    }

    /**
     * Возвращает наименование .
     * @return string
     */
    public function getCreatedByName()
    {
        return $this->created_by == null ? '' : ($this->createdBy->profile == null ? $this->createdBy->username : $this->createdBy->profile->name);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTr()
    {
        return $this->hasOne(TransportRequests::className(), ['id' => 'tr_id']);
    }
}
