<?php

namespace customer\models;

use Yii;
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
            // авторизоваться может только пользователь с полными правами или заказчик
            $user = $this->finder->findUserByUsernameOrEmail(trim($this->login));
            if ($user != null && (Yii::$app->authManager->checkAccess($user->id, 'root') || Yii::$app->authManager->checkAccess($user->id, 'customer')))
                return true;
            else
                $this->addError('password', 'За попытку авторизоваться за Вами уже выехала машина "Хлеб".');
        }

        return false;
    }
}
