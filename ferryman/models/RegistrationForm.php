<?php

namespace ferryman\models;

use Yii;
use common\models\User;
use common\models\FerrymenInvitations;
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
            'inviteLink'   => [['invite_id'], 'exist', 'skipOnError' => true, 'targetClass' => FerrymenInvitations::className(), 'targetAttribute' => ['invite_id' => 'id']],

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
        $this->username = '000';
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = Yii::createObject(User::className());
        $user->setScenario('register');
        $this->loadAttributes($user);
        $ferryman = $this->getInvitation()->ferryman;
        if ($ferryman == null || $ferryman->inn == null || trim($ferryman->inn) == '') return false;
        $user->username = User::FERRYMAN_LOGIN_PREFIX . trim($ferryman->inn);

        if (!$user->register()) {
            return false;
        }

        // удаляем приглашение
        $this->getInvitation()->delete();

        // дополним профиль зарегистрированного пользователя
        $role = Yii::$app->authManager->getRole('ferryman');
        Yii::$app->authManager->assign($role, $user->getId());
        $user->profile->name = $this->name;
        $user->profile->save();

        $ferryman->user_id = $user->getId();
        $ferryman->save(false);

        Yii::$app->session->setFlash(
            'info',
            'Ваш аккаунт был создан, и сообщение с учетными данными отправлено на ваш E-mail'
        );

        return true;
    }

    /**
     * @return FerrymenInvitations
     */
    public function getInvitation()
    {
        return FerrymenInvitations::findOne(['id' => $this->invite_id]);
    }
}
