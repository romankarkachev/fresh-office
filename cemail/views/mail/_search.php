<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\CEMailboxes;

/* @var $this yii\web\View */
/* @var $model common\models\CEMessagesSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */

$fieldSearchEntireTemplate = '{label}<div class="input-group">{input}<span class="input-group-btn">' .
    Html::submitButton('<i class="fa fa-search"></i>', ['id' => 'btnSearch', 'class' => 'btn btn-info', 'title' => 'Выполнить поиск']) .
    Html::a('<i class="fa fa-sort-amount-asc"></i>', ['/mail'], ['class' => 'btn btn-secondary', 'title' => 'Сбросить']) .
    '</span></div>{error}';
?>

<div class="cemessages-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/mail'],
        'method' => 'get',
        'options' => ['id' => 'frmSearch'],
    ]); ?>

    <div class="card card-accent-primary">
        <div class="card-block">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'mailbox_id')->widget(Select2::className(), [
                        'data' => CEMailboxes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>

                </div>
                <div class="col-md-10">
                    <?= $form->field($model, 'searchEntire', ['template' => $fieldSearchEntireTemplate])->textInput(['placeholder' => 'Введите значение для поиска']) ?>

                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
