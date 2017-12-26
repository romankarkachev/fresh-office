<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\PackingTypes;

/* @var $this yii\web\View */
/* @var $model common\models\PackingTypeReplaceForm */

$this->title = 'Замена дублирующихся видов упаковки | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']];
$this->params['breadcrumbs'][] = 'Замена дублирующихся видов упаковки';

$urlCountRows = Url::to(['/transport-requests/count-pt-rows']);
?>
<div class="transport-requests-packing-types-mass-replace">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'dest_pt_id')->widget(Select2::className(), [
                'data' => PackingTypes::arrayMapForSelect2(true),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => [
                    'placeholder' => '- выберите -',
                    'title' => 'Новый вид упаковки',
                ],
            ])->label('Применить вид упаковки') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'src_pt_id')->widget(Select2::className(), [
                'data' => PackingTypes::arrayMapForSelect2(true),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => [
                    'placeholder' => '- выберите -',
                    'title' => 'Старый вид упаковки',
                ],
                'pluginEvents' => [
                    'change' => 'function() {
    $.get("' . $urlCountRows . '?pt_id=" + $(this).val(), function(result) {
        if (result != false) $("#block-count-rows").text(result);
    });
}',
                ],
            ])->label('К строкам с видом') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'new_src_name')->textInput([
                'placeholder' => 'Введите новое наименование',
                'title' => 'Введите новое наименование вида упаковки, на который будет заменен текущий, если хотите его переименовать после выполнения операции',
            ])->label('Назначить наименование') ?>

        </div>
    </div>
    <div class="form-group"><div id="block-count-rows"></div></div>
    <div class="alert alert-warning" role="alert">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <strong>Обратите внимание!</strong>
        Новое наименование заполняйте <strong>только</strong> в том случае, если хотите переименовать вид упаковки, которым будет заменено. В большинстве случаев это поле будет оставаться пустым.
        <p>&laquo;Старый&raquo; (искомый) вид упаковки будет удален безвозратно, если замена будет произведена успешно.</p>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Запросы на транспорт', ['/transport-requests'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= Html::submitButton('<i class="fa fa-exclamation-circle" aria-hidden="true"></i> Выполнить замену', ['class' => 'btn btn-warning btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
