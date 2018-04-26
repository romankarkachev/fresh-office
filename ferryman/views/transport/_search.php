<?php

use yii\helpers\Html;
use common\components\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Ferrymen;

/* @var $this yii\web\View */
/* @var $model common\models\TransportSearch */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="transport-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/transport'],
        'method' => 'get',
    ]); ?>

    <div class="card">
        <div class="card-header card-header-info card-header-inverse"><i class="fa fa-filter"></i> Форма отбора</div>
        <div class="card-block">
            <div class="row">
                <?php if (Yii::$app->user->can('root')): ?>
                <div class="col-md-2">
                    <?= $form->field($model, 'ferryman_id')->widget(Select2::className(), [
                        'data' => Ferrymen::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <?php endif; ?>
                <div class="col-md-<?= Yii::$app->user->can('root') ? 10 : 12 ?>">
                    <?= $form->field($model, 'searchEntire')->textInput(['placeholder' => 'Введите значение поиска по всем полям']) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-filter"></i> Выполнить отбор', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/transport'], ['class' => 'btn btn-secondary']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
