<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Appeals;
use common\models\AppealSources;

/* @var $this yii\web\View */
/* @var $model common\models\Appeals */

$this->title = 'Новое обращение | ' . Yii::$app->name;
if (Yii::$app->user->can('root')) {
    $this->params['breadcrumbs'][] = ['label' => 'Обращения', 'url' => ['/appeals']];
}
$this->params['breadcrumbs'][] = 'Новое обращение *';
?>
<div class="appeals-create">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'form_company')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование компании']) ?>

                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'form_username')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя контактного лица']) ?>

                </div>
            </div>
            <?= $form->field($model, 'form_region')->textInput(['maxlength' => true, 'placeholder' => 'Введите название региона']) ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'form_phone', ['template' => '{label}<div class="input-group"><span class="input-group-addon">+7</span>{input}</div>{error}'])->widget(\yii\widgets\MaskedInput::className(), [
                        'mask' => '(999) 999-99-99',
                    ])->textInput(['maxlength' => true, 'placeholder' => 'Введите номер телефона']) ?>

                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'form_email')->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail']) ?>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'form_message')->textarea(['rows' => 5, 'placeholder' => 'Введите пожелания заказчика']) ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'as_id')->widget(Select2::className(), [
                        'data' => AppealSources::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'ac_id')->widget(Select2::className(), [
                        'data' => Appeals::arrayMapOfAccountSectionsForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
            </div>
        </div>
    </div>
    <p>Соберите все необходимые файлы в одном месте, нажмите на кнопку и единоразово отметьте все файлы. Вы можете прикрепить максимум 10 файлов.</p>
    <?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
