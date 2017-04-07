<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Url;
use dektrium\user\controllers\AdminController as BaseAdminController;
use dektrium\user\models\UserSearch;

class UsersController extends BaseAdminController
{
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
}