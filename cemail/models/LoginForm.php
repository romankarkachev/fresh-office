<?php

namespace cemail\models;

use Yii;
use common\models\CEUsersAccess;
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
            // авторизоваться может только пользователь с полными правами или пользователь, которому открыт хотя бы один ящик
            $user = $this->finder->findUserByUsernameOrEmail(trim($this->login));
            if ($user != null) {
                if (Yii::$app->authManager->checkAccess($user->id, 'root')) {
                    return true;
                }
                else {
                    // проверим, есть ли у этого пользователя доступ хотя бы к одному почтовому ящику
                    $availableMailboxes = CEUsersAccess::findAll(['user_id' => $user->id]);
                    if (count($availableMailboxes) > 0) {
                        return true;
                    }
                }
            }

            $this->addError('password', 'За попытку авторизоваться за Вами уже выехала машина "Хлеб".');
        }

        return false;
    }
}
