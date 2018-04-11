<?php

namespace ferryman\models;

use Yii;
use common\models\User;
use dektrium\user\models\LoginForm as BaseLoginForm;

/**
 * Расширение класса.
 * @author Roman Karkachev <post@romankarkachev.ru>
 */
class LoginForm extends BaseLoginForm
{
    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $user = $this->finder->findUserByUsernameOrEmail(trim($this->login));
            if ($user != null && Yii::$app->authManager->checkAccess($user->id, 'root')) return true;

            $this->user = $this->finder->findUserByUsernameOrEmail(User::FERRYMAN_LOGIN_PREFIX . trim($this->login));

            return true;
        } else {
            return false;
        }
    }
}
