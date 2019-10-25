<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use dektrium\user\models\UserSearch;
use common\models\DirectMSSQLQueries;
use common\models\Profile;
use common\models\UsersDepartments;
use common\models\UsersEiAccess;
use common\models\UsersEiApproved;
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