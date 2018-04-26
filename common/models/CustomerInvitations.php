<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "customer_invitations".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $fo_ca_id
 * @property string $email
 * @property string $token
 * @property integer $expires_at
 * @property integer $user_id
 *
 * @property User $user
 * @property User $createdBy
 */
class CustomerInvitations extends \yii\db\ActiveRecord
{
    /**
     * Количество секунд, через которое токен перестает действовать.
     */
    const TOKEN_EXPIRATION_TIME = 86400;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_invitations';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fo_ca_id', 'email', 'token', 'expires_at'], 'required'],
            [['created_at', 'created_by', 'fo_ca_id', 'expires_at', 'user_id'], 'integer'],
            [['email'], 'string', 'max' => 255],
            [['token'], 'string', 'max' => 32],
            [['token', 'user_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            ['email', 'filter', 'filter' => 'strtolower'],
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время отправки',
            'created_by' => 'Отправитель',
            'fo_ca_id' => 'Контрагент',
            'email' => 'E-mail',
            'token' => 'Токен',
            'expires_at' => 'Срок годности токена',
            'user_id' => 'Пользователь системы',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
