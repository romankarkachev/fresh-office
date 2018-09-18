<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ce_users_access".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $mailbox_id
 *
 * @property CEMailboxes $mailbox
 * @property User $user
 */
class CEUsersAccess extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ce_users_access';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'mailbox_id'], 'required'],
            [['user_id', 'mailbox_id'], 'integer'],
            [['mailbox_id'], 'exist', 'skipOnError' => true, 'targetClass' => CEMailboxes::className(), 'targetAttribute' => ['mailbox_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'mailbox_id' => 'Почтовый ящик',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailbox()
    {
        return $this->hasOne(CEMailboxes::className(), ['id' => 'mailbox_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
