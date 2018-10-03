<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Organizations;

/* @var $this yii\web\View */
/* @var $model common\models\LicensesRequests */

$this->title = 'Новый запрос | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Запросы лицензий', 'url' => ['/licenses-requests']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="licenses-requests-create">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'fkkosTextarea')->textarea(['rows' => 10, 'placeholder' => 'Введите коды ФККО по одному на строку']) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'tpFkkos', ['template' => "{error}"])->staticControl() ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'ca_email')->textInput(['placeholder' => 'E-mail контрагента']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'org_id')->widget(Select2::className(), [
                'data' => Organizations::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'receivers_email')->textInput(['placeholder' => 'E-mail получателя', 'title' => 'E-mail получателя сканов лицензий, куда они будут отправлены в случае одобрения запроса']) ?>

        </div>
    </div>
    <div class="form-group">
        <?php if (Yii::$app->user->can('root')): ?>
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Запросы лицензий', ['/licenses-requests'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php endif; ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
