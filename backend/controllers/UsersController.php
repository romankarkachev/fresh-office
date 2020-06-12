<?php

namespace backend\controllers;

use common\models\User;
use Yii;
use yii\helpers\Url;
use yii\web\Response;
use dektrium\user\models\UserSearch;
use common\models\DirectMSSQLQueries;
use common\models\Profile;
use common\models\UsersDepartments;
use common\models\UsersEiAccess;
use common\models\UsersEiApproved;
use common\models\UsersTrusted;
use common\models\UsersTrustedSearch;
use dektrium\user\controllers\AdminController as BaseAdminController;

class UsersController extends BaseAdminController
{
    /**
     * URL, ведущий в список записей без отбора
     */
    const ROOT_URL_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING];

    /**
     * URL, применяемый для сортировки и постраничного перехода
     */
    const ROOT_URL_FOR_SORT_PAGING = 'users';

    /**
     * Название пункта главного меню
     */
    const MAIN_MENU_LABEL = 'Пользователи';

    /**
     * URL для интерактивного создания доверенного лица пользователя
     */
    const URL_CREATE_TRUSTED_OTF = 'create-trusted-on-the-fly';
    const URL_CREATE_TRUSTED_OTF_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_CREATE_TRUSTED_OTF];

    /**
     * URL для интерактивного удаления доверенного лица пользователя
     */
    const URL_DELETE_TRUSTED_OTF = 'delete-trusted-on-the-fly';
    const URL_DELETE_TRUSTED_OTF_AS_ARRAY = ['/' . self::ROOT_URL_FOR_SORT_PAGING . '/' . self::URL_DELETE_TRUSTED_OTF];

    /**
     * Делает выборку доверенных лиц пользователя.
     * @param $user_id integer
     * @return \yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchTrusted($user_id)
    {
        $searchModel = new UsersTrustedSearch();
        $dataProvider = $searchModel->search([$searchModel->formName() => ['user_id' => $user_id]]);
        $dataProvider->sort = false;
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * Возвращает новую модель доверенного лица пользователя.
     * @param integer $user_id пользователь, которому добавляется доверенное лицо
     * @return UsersTrusted
     */
    private function createNewTrustedModel($user_id = null)
    {
        return new UsersTrusted([
            'user_id' => $user_id,
        ]);
    }

    /**
     * Рендерит список доверенных лиц пользователя.
     * @param integer $user_id идентификатор пользователя
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function renderTrusted($user_id)
    {
        return $this->renderAjax('trusted/_list', [
            'dataProvider' => $this->fetchTrusted($user_id),
            'model' => $this->createNewTrustedModel($user_id),
        ]);
    }

    /**
     * Функция выполняет поиск менеджера по наименованию, переданному в параметрах.
     * @param $q string
     * @return array
     */
    public function actionFreshOfficeManagersList($q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['results' => DirectMSSQLQueries::fetchManagers(true, $q)];
    }

    /**
     * @inheritdoc
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'actions-redirect');
        $searchModel  = \Yii::createObject(UserSearch::className());
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        $searchApplied = Yii::$app->request->get($searchModel->formName()) != null;

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'searchApplied' => $searchApplied,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        /** @var User $user */
        $user = \Yii::createObject([
            'class'    => User::class,
            'scenario' => 'create',
        ]);
        $event = $this->getUserEvent($user);

        $this->performAjaxValidation($user);

        $this->trigger(self::EVENT_BEFORE_CREATE, $event);
        if ($user->load(\Yii::$app->request->post()) && $user->create()) {
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been created'));
            $this->trigger(self::EVENT_AFTER_CREATE, $event);
            return $this->redirect(['update', 'id' => $user->id]);
        }

        return $this->render('create', [
            'user' => $user,
        ]);
    }

    /**
     * Updates an existing profile.
     * @param int $id
     * @return mixed
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdateProfile($id)
    {
        Url::remember('', 'actions-redirect');
        $user    = $this->findModel($id);
        $profile = $user->profile;

        if ($profile == null) {
            $profile = \Yii::createObject(Profile::class);
            $profile->link('user', $user);
        }
        $event = $this->getProfileEvent($profile);

        $this->performAjaxValidation($profile);

        $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);

        if ($profile->load(\Yii::$app->request->post())) {
            if ($profile->save()) {
                // заполнение отделов, к которым относится пользователь
                UsersDepartments::deleteAll(['user_id' => $id]);
                if (!empty($profile->departments)) {
                    foreach ($profile->departments as $department) {
                        (new UsersDepartments([
                            'user_id' => $id,
                            'department_id' => $department,
                        ]))->save();
                    }
                }

                // заполнение статей расходов, к которым имеет доступ сотрудник
                UsersEiAccess::deleteAll(['user_id' => $id]);
                if (!empty($profile->poEis)) {
                    foreach ($profile->poEis as $ei) {
                        (new UsersEiAccess([
                            'user_id' => $id,
                            'ei_id' => $ei,
                        ]))->save();
                    }
                }

                // заполнение статей расходов, к которым имеет доступ бухгалтер
                if (Yii::$app->getAuthManager()->getAssignment('accountant_b', $id)) {
                    UsersEiApproved::deleteAll(['user_id' => $id]);
                    if (!empty($profile->poEiForApproving)) {
                        foreach ($profile->poEiForApproving as $ei) {
                            (new UsersEiApproved([
                                'user_id' => $id,
                                'ei_id' => $ei,
                            ]))->save();
                        }
                    }
                }

                \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Profile details have been updated'));
                $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
                return $this->refresh();
            }
        }
        else {
            $profile->departments = UsersDepartments::find()->select('department_id')->where(['user_id' => $id])->asArray()->column();
            $profile->poEis = UsersEiAccess::find()->select('ei_id')->where(['user_id' => $id])->asArray()->column();
            $profile->poEiForApproving = UsersEiApproved::find()->select('ei_id')->where(['user_id' => $id])->asArray()->column();
        }

        return $this->render('_profile', [
            'user'    => $user,
            'profile' => $profile,
        ]);
    }

    /**
     * Страница "Доверенные лица пользователя"
     * @param $id integer идентификатор пользователя, доверенные лица которого необходимо отобразить
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionTrusted($id)
    {
        return $this->render('_trusted', ['user' => $this->findModel($id), 'model' => $this->createNewTrustedModel($id), 'dataProvider' => $this->fetchTrusted($id)]);
    }

    /**
     * Выполняет интерактивное добавление доверенного лица пользователя.
     * create-trusted-on-the-fly
     * @return mixed
     * @throws \Throwable
     */
    public function actionCreateTrustedOnTheFly()
    {
        if (Yii::$app->request->isPjax) {
            $model = new UsersTrusted();

            if ($model->load(Yii::$app->request->post())) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    if ($model->save()) {
                        $transaction->commit();
                        return $this->renderTrusted($model->user_id);
                    }
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                $transaction->rollBack();
            }
        }

        return false;
    }

    /**
     * Выполняет интерактивное удаление доверенного лица пользователя.
     * delete-waste
     * @param integer $id идентификатор пользователя
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionDeleteTrustedOnTheFly($id)
    {
        if (Yii::$app->request->isPjax) {
            $model = UsersTrusted::findOne($id);
            if ($model) {
                $user_id = $model->user_id;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->delete()) {
                        $transaction->commit();
                        return $this->renderTrusted($user_id);
                    }
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                $transaction->rollBack();
            }
        }

        return false;
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->checkIfUsed())
            return $this->render('@backend/views/common/cannot_delete', [
                'details' => [
                    'breadcrumbs' => ['label' => self::MAIN_MENU_LABEL, 'url' => self::ROOT_URL_AS_ARRAY],
                    'modelRep' => $model->name,
                    'buttonCaption' => self::MAIN_MENU_LABEL,
                    'buttonUrl' => self::ROOT_URL_AS_ARRAY,
                ],
            ]);

        $model->delete();

        return $this->redirect(self::ROOT_URL_AS_ARRAY);
    }
}