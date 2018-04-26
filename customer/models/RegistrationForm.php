<?php

namespace customer\models;

use Yii;
use common\models\User;
use common\models\CustomerInvitations;
use dektrium\user\models\RegistrationForm as BaseRegistrationForm;
use yii\helpers\ArrayHelper;

/**
 * Расширение класса.
 * @author Roman Karkachev <post@romankarkachev.ru>
 */
class RegistrationForm extends BaseRegistrationForm
{
    /**
     * @var integer идентификатор приглашения
     */
    public $invite_id;

    /**
     * @var string ФИО пользователя или наименование организации
     */
    public $name;

    /**
     * @var string подтверждение пароля
     */
    public $password_confirm;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            'invite'       => ['invite_id', 'integer'],
            'inviteLink'   => [['invite_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerInvitations::className(), 'targetAttribute' => ['invite_id' => 'id']],

            'nameLength'   => ['name', 'string', 'min' => 1, 'max' => 100],
            'nameTrim'     => ['name', 'filter', 'filter' => 'trim'],
            'nameRequired' => ['name', 'required'],

            'passwordConfirmLength' => ['password_confirm', 'string', 'min' => 6],
            'passwordConfirmRequired' => ['password_confirm', 'required'],
            'passwordConfirmCompare' => ['password_confirm', 'compare', 'skipOnEmpty' => false, 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'name' => 'Ваше имя',
            'email' => 'E-mail',
            'password_confirm' => 'Подтверждение пароля',
        ]);
    }

    /**
     * Registers a new user account. If registration was successful it will set flash message.
     * @return bool
     */
    public function register()
    {
        $this->username = $this->email;
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = Yii::createObject(User::className());
        $user->setScenario('register');
        $this->loadAttributes($user);

        if (!$user->registerCustomer()) {
            return false;
        }

        // дополним профиль зарегистрированного пользователя
        $role = Yii::$app->authManager->getRole('customer');
        Yii::$app->authManager->assign($role, $user->getId());
        $user->profile->name = $this->name;
        $user->profile->save();

        $invitation = $this->getInvitation();
        $invitation->user_id = $user->getId();
        $invitation->expires_at = time();
        $invitation->save(false);

        Yii::$app->session->setFlash(
            'info',
            'Ваш аккаунт был создан, и сообщение с учетными данными отправлено на ваш E-mail'
        );

        return true;
    }

    /**
     * @return CustomerInvitations
     */
    public function getInvitation()
    {
        return CustomerInvitations::findOne(['id' => $this->invite_id]);
    }
}
