<?php

namespace cemail\models;

use Yii;
use yii\base\Model;

/**
 * @property integer $user_id идентификатор пользователя этой системы
 * @property array $mailboxes массив почтовых ящиков, к которым у пользователя есть доступ
 */
class UserAccessForm extends Model
{
    public $user_id;

    public $mailboxes;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['mailboxes'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Пользователь',
            'mailboxes' => 'E-mails',
        ];
    }
}
