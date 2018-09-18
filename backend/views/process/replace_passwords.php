<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use backend\components\grid\GridView;
use kartik\select2\Select2;
use common\models\AuthItem;

/* @var $this yii\web\View */
/* @var $model common\models\ReplacePasswordsForm */
/* @var $dataProvider \yii\data\ActiveDataProvider таблица отобранных пользователей */

$this->title = 'Массовая замена паролей пользователей | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Замена паролей пользователей';
?>
<div class="replace-passwords-form">
    <?php $form = ActiveForm::begin(['id' => 'frmFilter', 'action' => ['/process/replace-passwords'], 'method' => 'get']); ?>

    <p>Форма предназначена для массовой замены паролей у пользователей, отобранных по условиям.</p>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'passwordLength')->widget(MaskedInput::className(), [
                'mask' => '9',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите длину', 'title' => 'Введите длину будущих паролей']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'conditionRoleId')->widget(Select2::className(), [
                'data' => AuthItem::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите роль -'],
                'hideSearch' => true,
            ]); ?>

        </div>
        <div class="col-md-2">
            <label for="<?= strtolower($model->formName() . '-conditionOnlyFO') ?>" class="control-label"><?= $model->getAttributeLabel('conditionOnlyFO') ?></label>
            <?= $form->field($model, 'conditionOnlyFO')->checkbox()->label(false) ?>

        </div>
    </div>

    <?= Html::submitButton('<i class="fa fa-filter"></i> Применить фильтр', ['class' => 'btn btn-info']) ?>

    <?php ActiveForm::end(); ?>

    <?php $form = ActiveForm::begin(['id' => 'frmProcess']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'header' => 'Отметка',
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\User */
                    /* @var $column \yii\grid\DataColumn */

                    return ['value' => $model->id, 'checked' => 'checked'];
                },
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            /*
            [
                'attribute' => 'id',
                'label' => 'ID',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\User */
                    /* @var $column \yii\grid\DataColumn */

                    /*
                    return $model['id'] . Html::hiddenInput('ReplaceUsersPasswords[]', $model['id']);
                },
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            */
            [
                'attribute' => 'username',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\User */
                    /* @var $column \yii\grid\DataColumn */

                    $icon = '';
                    if ($model['fo_id'] != null) $icon = ' ' . Html::img(Url::to(['/images/freshoffice16.png']), ['title' => 'Пользователь привязан к учетной записи во Fresh Office']);

                    return $model[$column->attribute] . $icon;
                },
            ],
            'profileName',
            'roleName',
            [
                'attribute' => 'newPassword',
                //'label' => 'ID',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\User */
                    /* @var $column \yii\grid\DataColumn */

                    return $model['newPassword'] . Html::hiddenInput('ReplaceUsersPasswords[' . $model['id'] . ']', $model['newPassword']);
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

    <?php if ($dataProvider->totalCount > 0): ?>
    <p>В таблице всего <?= common\models\foProjects::declension($dataProvider->totalCount, ['пользователя','пользователей','пользователей']) ?>.</p>
    <?= Html::submitButton('<i class="fa fa-cog"></i> Выполнить замену', ['class' => 'btn btn-success btn-lg']) ?>

    <?php endif; ?>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});

// Функция перехватывает отправку формы на сервер и просит пользователя подтвердить ее.
//
function formSubmit() {
    return confirm("Вы действительно хотите изменить пароли всем отмеченным пользователя?");
} // formSubmit()

$(document).on("submit", "#frmProcess", formSubmit);
JS
, \yii\web\View::POS_READY);
?>
