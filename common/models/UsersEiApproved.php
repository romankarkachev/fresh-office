<?php

namespace common\models;

use Yii;

/**
 * Таблица содержит статьи расходов, доступные пользователям к согласованию без руководства.
 *
 * @property int $id
 * @property int $user_id Пользователь
 * @property int $ei_id Статья расходов
 *
 * @property PoEi $ei
 * @property User $user
 */
class UsersEiApproved extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users_ei_aa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'ei_id'], 'required'],
            [['user_id', 'ei_id'], 'integer'],
            [['ei_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoEi::class, 'targetAttribute' => ['ei_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
        return $this->hasOne(PoEi::class, ['id' => 'ei_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
