<?php

use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var yii\widgets\ActiveForm $form */
/* @var common\models\Profile $model */

$this->title = Yii::t('user', 'Profile settings');
$this->params['breadcrumbs'][] = $this->title;

$this->params['content-block'] = 'Профиль';
$this->params['content-additional'] = 'Ваш персональный раздел, где Вы можете задать информацию о себе, прикрепить аватар, изменить пароль.';

$pifp = Yii::getAlias('@uploads-profiles').'/';
$avatar = '';
if ($model->avatar_fn != null && $model->avatar_fn != '')
    if ($model->avatar_ffp != null && $model->avatar_ffp != '')
        if (file_exists($model->avatar_ffp)) {
            $avatar = '<p class="text-center"><img src="'.$pifp.$model->avatar_fn.'" height="200" /></p>';
        }
?>

<div class="row">
    <div class="col-md-3">
        <?= $this->render('_menu') ?>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?= Html::encode($this->title) ?>
            </div>
            <div class="panel-body">
                <?= $avatar ?>
                <?php $form = \yii\widgets\ActiveForm::begin([
                    'id' => 'profile-form',
                    'options' => ['class' => 'form-horizontal'],
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-sm-offset-3 col-lg-9\">{error}\n{hint}</div>",
                        'labelOptions' => ['class' => 'col-lg-3 control-label'],
                    ],
                    'enableAjaxValidation'   => true,
                    'enableClientValidation' => false,
                    'validateOnBlur'         => false,
                ]); ?>

                <?= $form->field($model, 'imageFile')->fileInput() ?>

                <?= $form->field($model, 'name') ?>

                <?= $form->field($model, 'public_email') ?>

                <?= $form->field($model, 'website') ?>

                <?= $form->field($model, 'location') ?>

                <?= $form
                    ->field($model, 'timezone')
                    ->dropDownList(
                        \yii\helpers\ArrayHelper::map(
                            \dektrium\user\helpers\Timezone::getAll(),
                            'timezone',
                            'name'
                        )
                    ); ?>

                <?= $form->field($model, 'bio')->textarea() ?>

                <div class="form-group">
                    <div class="col-lg-offset-3 col-lg-9">
                        <?= \yii\helpers\Html::submitButton(
                            Yii::t('user', 'Save'),
                            ['class' => 'btn btn-block btn-success']
                        ) ?><br>
                    </div>
                </div>

                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
