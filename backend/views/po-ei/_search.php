<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\PoEiController;

/* @var $this yii\web\View */
/* @var $model common\models\PoEiSearch */
/* @var $searchApplied bool */
?>

<div class="po-ei-search">
    <?php $form = ActiveForm::begin([
        'action' => PoEiController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
        'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'group_id')->widget(Select2::className(), [
                        'data' => \common\models\PoEig::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'name')->textInput(['placeholder' => 'Введите наименование']) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', PoEiController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
