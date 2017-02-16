<?php

namespace backend\controllers;

use dektrium\user\controllers\SecurityController as BaseSecurity;

class SecurityController extends BaseSecurity
{
    /**
     * @inheritdoc
     */
    public function actionLogin()
    {
        // макет для неавторизованного пользователя
        $this->layout = '//na';

        return parent::actionLogin();
    }
}