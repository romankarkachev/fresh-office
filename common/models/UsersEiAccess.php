<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "users_ei_access".
 *
 * @property int $id
 * @property int $user_id Пользователь
 * @property int $ei_id Статья расходов
 *
 * @property PoEi $ei
 * @property User $user
 */
class UsersEiAccess extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users_ei_access';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'ei_id'], 'required'],
            [['user_id', 'ei_id'], 'integer'],
            [['ei_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoEi::className(), 'targetAttribute' => ['ei_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'ei_id' => 'Статья расходов',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEi()
    {
        return $this->hasOne(PoEi::className(), ['id' => 'ei_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
