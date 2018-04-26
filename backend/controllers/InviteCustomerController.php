<?php

namespace backend\controllers;

use Yii;
use yii\bootstrap\ActiveForm;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use backend\models\CustomerInvitationForm;

/**
 * Отправка приглашений зарегистрироваться в личном кабинете клиентам.
 */
class InviteCustomerController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'validate-customer-invitation-form', 'compose-fields'],
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'send-customer-invitation' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Формирует поля Водитель и Транспорт для выбранного пользователем перевозчика.
     * @param $ca_id integer идентификатор клиента
     * @return mixed
     */
    public function actionComposeFields($ca_id)
    {
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_invite_fields', [
                'model' => new CustomerInvitationForm(),
                'emails' => CustomerInvitationForm::arrayMapOfEmailsForSelect2($ca_id),
                'form' => ActiveForm::begin(),
            ]);
        }

        return false;
    }

    /**
     * Отображает форму отправки клиенту приглашения зарегистрироваться в личном кабинете.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new CustomerInvitationForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendInvitation()) {
                Yii::$app->session->setFlash('success', 'Приглашение зарегистрироваться успешно отправлено.');
                return $this->redirect(['/invite-customer']);
            }
            else
                Yii::$app->session->setFlash('error', 'Не удалось отправить клиенту приглашение зарегистрироваться.');
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * AJAX-валидация формы отправки приглашения клиенту.
     */
    public function actionValidateCustomerInvitationForm()
    {
        $model = new CustomerInvitationForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            echo Json::encode(ActiveForm::validate($model));
            Yii::$app->end();
        }
    }
}
