<?php

namespace backend\controllers;


use Yii;
use yii\helpers\Url;
use yii\web\Response;
use dektrium\user\models\UserSearch;
use common\models\DirectMSSQLQueries;
use dektrium\user\controllers\AdminController as BaseAdminController;

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
}