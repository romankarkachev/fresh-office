<?php

use yii\bootstrap\ActiveForm;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\FinishEdfForm */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="finish-edf-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmFinishEdf',
        'action' => '/edf/finish-edf',
    ]); ?>

    <?= GridView::widget([
        'id' => 'gwFilesToStorage',
        'dataProvider' => $model->edfFiles,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped'],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'name' => $model->formName() . '[files][]',
                'header' => 'Отметка',
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\EdfFiles */
                    /* @var $column \yii\grid\DataColumn */

                    return ['value' => $model->id];
                },
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'ofn',
                'enableSorting' => false,
            ],
            [
                'attribute' => 'uploaded_at',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                'format' =>  ['date', 'dd.MM.Y HH:mm'],
                'options' => ['width' => '130'],
                'enableSorting' => false,
            ],
            [
                'attribute' => 'uploadedByProfileName',
                'enableSorting' => false,
            ],
        ],
    ]); ?>

    <?= $form->field($model, 'edf_id')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});
JS
, \yii\web\View::POS_READY);
?>
