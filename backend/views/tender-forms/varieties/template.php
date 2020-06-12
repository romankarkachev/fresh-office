<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\bootstrap\ActiveForm;
use backend\controllers\TenderFormsController;

/* @var $this yii\web\View */
/* @var $model common\models\TenderFormsTemplateForm */
/* @var $form yii\bootstrap\ActiveForm */

$varietyName = $model->varietyName;
$kindName = $model->kindName;
$this->title = $kindName . HtmlPurifier::process(' &mdash; ' . TenderFormsController::LABEL_VARIETIES . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'] = TenderFormsController::BREADCRUMBS_VARIETIES;
$this->params['breadcrumbs'][] = 'Обновление шаблона формы';
?>
<?php $form = ActiveForm::begin(); ?>

<h3>Обновление файла шаблона формы</h3>
<p><?= $varietyName ?> &mdash; <?= $kindName ?></p>
<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'file')->fileInput()->label('Выберите файл шаблона') ?>

    </div>
</div>
<div class="form-group">
    <?= Html::submitButton('Обновить', ['class' => 'btn btn-default']) ?>

</div>

<?php ActiveForm::end(); ?>
