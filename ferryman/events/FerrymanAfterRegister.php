<?php

namespace ferryman\events;

use Yii;

/**
 * Событие вызывается после регистрации пользователя в подсистеме для перевозчиков.
 * @author Roman Karkachev <post@romankarkachev.ru>
 */
class FerrymanAfterRegister
{
    /**
     * Сразу после успешной регистрации авторизует пользователя в системе.
     * @param $event \dektrium\user\events\FormEvent
     */
    public static function handleAfterRegister($event)
    {
        $user = \common\models\User::findOne(['email' => $event->form->email]);
        if ($user) {
            Yii::$app->user->switchIdentity($user);
            Yii::$app->response->redirect(Yii::$app->user->returnUrl);
        }
    }
}
