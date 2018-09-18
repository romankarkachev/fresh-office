<?php

namespace cemail\controllers;

use Yii;
use cemail\models\UserAccessForm;
use common\models\User;
use common\models\CEMailboxes;
use common\models\CEUsersAccess;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Управление доступом пользователей в систему.
 */
class UsersAccessController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'render-user-mailboxes'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['root'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Отображает страницу управления доступом пользователей.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new UserAccessForm();

        if ($model->load(Yii::$app->request->post())) {
            $user = User::findOne($model->user_id);
            if ($user) {
                $receivedCount = count($model->mailboxes);
                $successCount = 0;

                // удаляем все имеющиеся записи
                CEUsersAccess::deleteAll(['user_id' => $model->user_id]);

                // ...и делаем новые :)
                if (!empty($model->mailboxes)) {
                    foreach ($model->mailboxes as $mailbox) {
                        $access = new CEUsersAccess([
                            'user_id' => $model->user_id,
                            'mailbox_id' => $mailbox,
                        ]);
                        if ($access->save()) $successCount++;
                    }
                }

                if ($receivedCount == $successCount) Yii::$app->session->setFlash('success', 'Доступ успешно назначен.');
                $model->user_id = null;
            }
        }

        return $this->render('index', ['model' => $model]);
    }

    /**
     * @param $user_id integer идентификатор пользователя, почтовые ящики которого необходимо отметить
     * @return mixed
     */
    public function actionRenderUserMailboxes($user_id)
    {
        if (Yii::$app->request->isAjax) {
            $user = User::findOne($user_id);
            if ($user) {
                return $this->renderAjax('_mailboxes', [
                    'model' => new UserAccessForm(),
                    'form' => new  \yii\bootstrap\ActiveForm(),
                    'mailboxes' => CEMailboxes::arrayMapForSelect2(),
                    'userAccess' => CEUsersAccess::find()->select('mailbox_id')->where(['user_id' => $user_id])->asArray()->column(),
                ]);
            }
        }
    }
}
