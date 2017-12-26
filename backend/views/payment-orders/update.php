<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrders */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$this->title = $model->modelRep . HtmlPurifier::process(' &mdash; Платежные ордеры | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Платежные ордеры', 'url' => ['/payment-orders']];
$this->params['breadcrumbs'][] = $model->modelRep;
?>
<div class="payment-orders-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(['/payment-orders/upload-files']),
            'uploadExtraData' => [
                'obj_id' => $model->id,
            ],
        ]
    ]) ?>

</div>
<?php
$this->registerJs(<<<JS
$("#new_files").on("filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#afs"});
});
JS
, \yii\web\View::POS_READY);
?>
