<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use backend\components\grid\GridView;
use backend\controllers\OrganizationsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\OrganizationsBasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\OrganizationsBas */
/* @var $action string */
?>
<div class="bank-accounts-list">
    <?php Pjax::begin(['id' => 'pjax-bank-accounts' . $action, 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= $this->render('_bank_account_form', [
        'dataProvider' => $dataProvider,
        'model' => $model,
        'action' => $action,
    ]); ?>

    <?= GridView::widget([
        'id' => 'gw-bank-accounts',
        'dataProvider' => $dataProvider,
        'layout' => '{items}',
        'columns' => [
            [
                'header' => 'Реквизиты',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\OrganizationsBas */
                    /* @var $column \yii\grid\DataColumn */

                    $result = [];

                    if ($model->bank_an != null) $result[] = '<strong>Счет №</strong> ' . $model->bank_an;
                    if ($model->bank_name != null) $result[] = '<strong>банк</strong> ' . $model->bank_name;
                    if ($model->bank_bik != null) $result[] = '<strong>БИК</strong> ' . $model->bank_bik;
                    if ($model->bank_ca != null) $result[] = '<strong>корр. счет</strong> ' . $model->bank_ca;

                    if (count($result) > 0)
                        return implode(', ', $result);
                    else
                        return '';
                }
            ],
            [
                'class' => 'backend\components\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', [OrganizationsController::URL_DELETE_BANK_ACCOUNT, 'id' => $model->id], ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => true]);
                    }
                ],
                'options' => ['width' => '20'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
