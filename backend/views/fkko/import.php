<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\FkkoImport */

$this->title = 'Импорт кодов ФККО | '.Yii::$app->name;
$this->blocks['content-header'] = 'Импорт кодов ФККО';
$this->params['breadcrumbs'][] = ['label' => 'Коды ФККО', 'url' => ['/fkko']];
$this->params['breadcrumbs'][] = 'Импорт';
?>
<div class="fkko-import">
    <div class="card card-accent-primary">
        <div class="card-header">Примечание</div>
        <div class="card-block">
            <p>Внимание! В файле импорта первая строка должна содержать заголовок. Наименования полей стандартизированы, они указаны в скобках, только латинские символы. Обратите внимание, что сопоставление объектов осуществляется с учетом регистра: &laquo;Директор&raquo; и &laquo;директор&raquo; - разные данные.</p>
            <p><strong>Обратите также внимание</strong>, что файл импорта, который Вы предоставляете, должен содержать только один лист в книге. В противном случае импорт не может быть выполнен.</p>
            <p>Файл импорта должен содержать следующие поля (порядок может быть любой): <strong>Код ФККО-2017 *</strong> (fkko), <strong>Наименование *</strong> (name).</p>
        </div>
    </div>
    <?php $form = ActiveForm::begin() ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'importFile')->fileInput()->label(false) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Коды ФККО', ['/fkko'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= Html::submitButton('<i class="fa fa-cloud-upload" aria-hidden="true"></i> Выполнить', ['class' => 'btn btn-success btn-lg']) ?>

    </div>
    <?php ActiveForm::end() ?>

</div>