<?php

namespace common\components;

use Yii;
use dektrium\user\models\Token;
use dektrium\user\models\User;
use dektrium\user\Mailer as BaseMailer;

/**
 * Расширение класса.
 * @author Roman Karkachev <post@romankarkachev.ru>
 */
class Mailer extends BaseMailer
{
    /**
     * Отправляет сообщение пользователю после его регистрации.
     * @param User  $user
     * @param Token $token
     * @param bool  $showPassword
     * @return bool
     */
    public function sendWelcomeMessage(User $user, Token $token = null, $showPassword = false)
    {
        return $this->sendMessage(
            $user->email,
            $this->getWelcomeSubject(),
            'ferrymanWelcome-html',
            ['user' => $user, 'token' => $token, 'module' => $this->module, 'showPassword' => $showPassword]
        );
    }

    /**
     * Отправляет сообщение клиенту после его регистрации в личном кабинете.
     * @param User  $user
     * @param Token $token
     * @param bool  $showPassword
     * @return bool
     */
    public function sendWelcomeMessageCustomer(User $user, Token $token = null, $showPassword = false)
    {
        return $this->sendMessage(
            $user->email,
            $this->getWelcomeSubject(),
            'customerWelcome-html',
            ['user' => $user, 'token' => $token, 'module' => $this->module, 'showPassword' => $showPassword]
        );
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array  $params
     *
     * @return bool
     */
    protected function sendMessage($to, $subject, $view, $params = [])
    {
        /** @var $mailer \yii\mail\BaseMailer */
        $mailer = Yii::$app->mailer;
        $mailer->viewPath = $this->viewPath;
        $mailer->getView()->theme = Yii::$app->view->theme;

        if ($this->sender === null) {
            $this->sender = isset(Yii::$app->params['senderEmail']) && isset(Yii::$app->params['senderMelania']) ?
                [Yii::$app->params['senderEmail'] => Yii::$app->params['senderMelania']]
                : 'no-reply@example.com';
        }

        return $mailer->compose(['html' => $view], $params)
            ->setTo($to)
            ->setFrom($this->sender)
            ->setSubject($subject)
            ->send();
    }
}
