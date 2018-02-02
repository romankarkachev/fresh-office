<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model backend\models\PaymentOrdersImport */

$this->title = 'Импорт платежных ордеров | '.Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Платежные ордеры', 'url' => ['/payment-orders']];
$this->params['breadcrumbs'][] = 'Импорт платежных ордеров';
?>
<div class="freightspayments-import">
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">Примечание</h3>
        </div>
        <div class="box-body">
            <p>Внимание! В файле импорта первая строка должна содержать заголовок.</p>
            <p>Файл импорта должен содержать следующие поля:
                <strong>ID проекта *</strong> (колонка C),
                <strong>Контрагент *</strong> (колонка G),
                <strong>Сумма *</strong> (колонка I, представляет собой поле Себестоимость),
                <strong>Перевозчик *</strong> (колонка M),
                ТТН (колонка O).
            </p>
            <p><strong>Обратите также внимание</strong>, что файл импорта, который Вы предоставляете, должен содержать только один лист в книге. В противном случае импорт не может быть выполнен.</p>
        </div>
    </div>
    <?php $form = ActiveForm::begin() ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'importFile')->fileInput() ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-cloud-upload" aria-hidden="true"></i> Выполнить', ['class' => 'btn btn-success btn-lg']) ?>

    </div>
    <?php ActiveForm::end() ?>

</div>