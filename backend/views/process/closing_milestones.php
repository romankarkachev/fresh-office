<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\ClosingMilestonesForm */

$this->title = 'Закрытие этапов проектов | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Закрытие этапов проектов';
?>
<div class="ferrymen-form">
    <?php $form = ActiveForm::begin(); ?>

    <p>Форма предназначена для закрытия этапов в проектах.</p>
    <p>Выберите дату, применяется инклюзивный способв закрытия этапов, то есть включительно до конца выбранного дня.</p>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'date_finish')->widget(DateControl::className(), [
                'value' => $model->date_finish,
                'type' => DateControl::FORMAT_DATE,
                'language' => 'ru',
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => '- выберите -'],
                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                    'layout' => '<div class="input-group">{input}{picker}</div>',
                    'pluginOptions' => [
                        'todayHighlight' => true,
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
    </div>
    <?= Html::submitButton('<i class="fa fa-cog"></i> Выполнить', ['class' => 'btn btn-success btn-lg']) ?>

    <?php ActiveForm::end(); ?>

</div>