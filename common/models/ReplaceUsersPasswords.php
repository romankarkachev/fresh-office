<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * @property integer $id идентификатор пользователя
 * @property string $username логин пользователя
 * @property string $profileName имя пользователя
 * @property string $roleName роль
 * @property string $newPassword
 */
class ReplaceUsersPasswords extends Model
{
    public $id;

    public $username;

    public $profileName;

    public $roleName;

    public $newPassword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['username', 'profileName', 'roleName'], 'string'],
            [['newPassword'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Логин',
            'profileName' => 'Имя',
            'roleName' => 'Роль',
            'newPassword' => 'Новый пароль',
        ];
    }
}
