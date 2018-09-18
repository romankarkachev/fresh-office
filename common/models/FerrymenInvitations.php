<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ferrymen_invitations".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $ferryman_id
 * @property string $email
 * @property string $token
 * @property integer $expires_at
 *
 * @property Ferrymen $ferryman
 * @property User $createdBy
 */
class FerrymenInvitations extends \yii\db\ActiveRecord
{
    /**
     * Количество секунд, через которое токен перестает действовать.
     */
    const TOKEN_EXPIRATION_TIME = 86400 * 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ferrymen_invitations';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id', 'email', 'token', 'expires_at'], 'required'],
            [['created_at', 'created_by', 'ferryman_id', 'expires_at'], 'integer'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['token'], 'string', 'max' => 32],
            [['token'], 'unique'],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
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
            'ferryman_id' => 'Перевозчик',
            'email' => 'E-mail получателя',
            'token' => 'Токен',
            'expires_at' => 'Срок годности токена',
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
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::className(), ['id' => 'ferryman_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
