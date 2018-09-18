<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use yii\widgets\Pjax;
use backend\controllers\PbxWebsitesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\pbxExternalPhoneNumberSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\pbxExternalPhoneNumber */
/* @var $action string */
?>
<div class="pbx-external-phone-number-list">
    <?php Pjax::begin(['id' => 'pjax-dialogs' . $action, 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= $this->render('_external_phone_form', [
        'dataProvider' => $dataProvider,
        'model' => $model,
        'action' => $action,
    ]); ?>

    <?= GridView::widget([
        'id' => 'gw-phones',
        'dataProvider' => $dataProvider,
        'layout' => '{items}',
        'columns' => [
            'phone_number',
            [
                'class' => 'backend\components\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', [PbxWebsitesController::URL_DELETE_EXTERNAL_PHONE, 'id' => $model->id], ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => true]);
                    }
                ],
                'options' => ['width' => '20'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
