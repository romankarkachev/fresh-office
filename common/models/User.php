<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use dektrium\user\helpers\Password;
use common\models\Profile;
use dektrium\user\models\User as BaseUser;

/**
 * @property AuthItem $role
 *
 * @property Profile $profile
 */
class User extends BaseUser
{
    /**
     * Имя.
     * @var string
     */
    public $name;

    /**
     * Идентификатор пользователя во Fresh Office.
     * @var integer
     */
    public $fo_id;

    /**
     * Роль.
     * @var string
     */
    public $role_id;

    /**
     * Подтверждение пароля.
     * @var string
     */
    public $password_confirm;

    /**
     * ФИО пользователя (для вложенного запроса и сортировки).
     */
    public $profileName;

    /**
     * Описание роли пользователя (для вложенного запроса и сортировки).
     * @var string
     */
    public $roleName;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['name', 'role_id'], 'required', 'on' => 'create'];
        $rules[] = [['username', 'email'], 'required', 'on' => 'update'];
        $rules[] = [['fo_id'], 'integer'];
        $rules[] = [['role_id'], 'string'];
        $rules['password_confirm'] = ['password', 'string', 'min' => 6];
        $rules[] = ['password_confirm', 'required', 'on' => 'create'];
        $rules[] = ['password_confirm', 'compare', 'skipOnEmpty' => false, 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают', 'on' => 'create'];
        $rules[] = [['name'], 'string', 'max' => 255];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $result = parent::attributeLabels();

        $result['email'] = 'E-mail';
        $result['fo_id'] = 'Пользователь Fresh Office';
        $result['name'] = 'ФИО';
        $result['role_id'] = 'Роль';
        $result['password_confirm'] = 'Подтверждение пароля';
        // для сортировки
        $result['profileName'] = 'ФИО';
        $result['roleName'] = 'Роль';

        return $result;
    }

    /**
     * Creates new user account. It generates password if it is not provided by user.
     *
     * @return bool
     */
    public function create()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        $this->confirmed_at = time();
        $this->password = $this->password == null ? Password::generate(8) : $this->password;
        $password = $this->password;

        $this->trigger(self::BEFORE_CREATE);

        if (!$this->save()) {
            return false;
        }

        $this->trigger(self::AFTER_CREATE);

        // задание роли
        $role = Yii::$app->authManager->getRole($this->role_id);
        Yii::$app->authManager->assign($role, $this->getId());

        // заполнение профиля
        $this->profile->name = $this->name;
        $this->profile->fo_id = $this->fo_id;
        $this->profile->save();

        return true;
    }

    /**
     * Делает выборку пользователей веб-приложения с ролью "Менеджер" и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(User::find()->select(User::tableName().'.*')
            ->leftJoin('`auth_assignment`', '`auth_assignment`.`user_id`='.User::tableName().'.`id`')
            ->leftJoin('`profile`', '`profile`.`user_id` = `user`.`id`')
            ->where(['`auth_assignment`.`item_name`' => 'sales_department_manager'])->orderBy('profile.name')->all(), 'id', 'profile.name');
    }

    /**
     * Делает запрос с целью установления наименования менеджера по имеющемуся идентификатору.
     * @param $id integer идентификатор менеджера
     * @return string
     */
    public static function getFreshOfficeManagerName($id)
    {
        $man = DirectMSSQLQueries::fetchManager($id);
        if (is_array($man)) if (count($man) > 0) return $man[0]['name'];
        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращает ФИО пользователя.
     * @return string
     */
    public function getProfileName()
    {
        return $this->profile == null ? '' : $this->profile->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoles()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'item_name'])
            ->via('userRoles');
    }

    /**
     * Возвращает наименование роли пользователя.
     * @return string
     */
    public function getRoleName()
    {
        return $this->role != null ? $this->role->name : '';
    }

    /**
     * Возвращает описание роли пользователя.
     * @return string
     */
    public function getRoleDescription()
    {
        return $this->role != null ? $this->role->description : '';
    }
}