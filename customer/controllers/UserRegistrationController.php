<?php

namespace customer\controllers;

use Yii;
use common\models\CustomerInvitations;
use customer\models\RegistrationForm;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use dektrium\user\controllers\RegistrationController as BaseRegistrationController;

/**
 * Расширение класса.
 * @author Roman Karkachev <post@romankarkachev.ru>
 */
class UserRegistrationController extends BaseRegistrationController
{
    /**
     * Displays the registration page.
     * After successful registration if enableConfirmation is enabled shows info message otherwise
     * redirects to home page.
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionRegister()
    {
        $this->layout = '//error';
        $invite = null;

        /* @var RegistrationForm $model */
        $model = Yii::createObject(RegistrationForm::className());

        $event = $this->getFormEvent($model);

        $this->trigger(self::EVENT_BEFORE_REGISTER, $event);

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->register()) {
                $this->trigger(self::EVENT_AFTER_REGISTER, $event);

                return $this->render('/message', [
                    'title' => 'Ваш аккаунт был создан, и сообщение с учетными данными отправлено на ваш mail',
                    'heading' => 'Регистрация',
                    'module' => $this->module,
                    'force' => true,
                ]);
            }
        }
        $token = Yii::$app->request->get('token');
        if (Yii::$app->request->isGet)
            if ($token != null) {
                $invite = CustomerInvitations::findOne(['token' => $token]);
                if ($invite != null) {
                    if ($invite->expires_at >= time()) {
                        $model->invite_id = $invite->id;
                        $model->email = $invite->email;
                    }
                    else return $this->render('//default/error', [
                        'exception' => new BadRequestHttpException(),
                        'message' => 'Срок действия приглашения истек. Запросите у менеджера новое приглашение.',
                    ]);
                }
                else return $this->render('//default/error', [
                    'exception' => new NotFoundHttpException(),
                    'message' => 'Приглашение не обнаружено. Продолжение невозможно.',
                ]);
            }
            else return $this->render('//default/error', [
                'exception' => new BadRequestHttpException(),
                'message' => 'По данной ссылке зарегистрироваться невозможно.',
            ]);

        return $this->render('register', [
            'model' => $model,
            'module' => $this->module,
            'invite' => $invite,
        ]);
    }
}
