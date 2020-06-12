<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Ferrymen;
use common\models\LoadTypes;

/* @var $this yii\web\View */
/* @var $model common\models\TransportSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="transport-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/ferrymen-transport'],
        'method' => 'get',
        //'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'ferryman_id')->widget(Select2::className(), [
                        'data' => Ferrymen::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'searchEntire')->textInput(['placeholder' => 'Введите значение поиска по всем полям']) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'searchLoadTypes')->widget(Select2::classname(), [
                        'data' => LoadTypes::arrayMapForSelect2(),
                        'value' => $model->searchLoadTypes,
                        'options' => ['placeholder' => 'Выберите доступные способы погрузки', 'multiple' => true],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                            'maximumInputLength' => 10
                        ],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchDopog')->widget(Select2::className(), [
                        'data' => \common\models\TransportSearch::arrayMapOfDopogForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/ferrymen-transport'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
