<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use backend\components\grid\GridView;
use backend\controllers\EcoProjectsController;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\EcoProjectsAccess */
/* @var $action string */
?>
<div class="user-access-list">
    <p class="lead">
        Доступ к проекту имеют пользователи с полными правами, руководители отдела экологии, пользователи из списка ниже.
        Руководители отдела продаж имеют доступ к созданным проектам лично, а также к проектам, созданным менеджерами.
        Экологи и менеджеры имеют доступ только к тем проектам, которые создавали сами или доступ к которым им был
        предоставлен через этот инструмент.
    </p>
    <?php Pjax::begin(['id' => 'pjax-user-access' . $action, 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <div class="panel panel-default">
        <div class="panel-body">
            <?= GridView::widget([
                'id' => 'gw-userAccess',
                'dataProvider' => $dataProvider,
                'layout' => '{items}',
                'columns' => [
                    'userProfileName',
                    [
                        'class' => 'backend\components\grid\ActionColumn',
                        'template' => '{delete}',
                        'buttons' => [
                            'delete' => function ($url, $model) {
                                return Html::a('<i class="fa fa-trash-o"></i>', [EcoProjectsController::URL_DELETE_USER_ACCESS, 'id' => $model->id], ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => true]);
                            }
                        ],
                        'options' => ['width' => '20'],
                    ],
                ],
            ]); ?>

        </div>
    </div>
    <?= $this->render('_access_form', [
        'dataProvider' => $dataProvider,
        'model' => $model,
        'action' => $action,
    ]); ?>

    <?php Pjax::end(); ?>

</div>
