<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\ProductsImport;

/* @var $this yii\web\View */
/* @var $model common\models\ProductsImport */

$this->title = 'Импорт номенклатуры | '.Yii::$app->name;
$this->blocks['content-header'] = 'Импорт номенклатуры';
$this->params['breadcrumbs'][] = ['label' => 'Номенклатура', 'url' => ['/products']];
$this->params['breadcrumbs'][] = 'Импорт';
?>
<div class="products-import">
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">Примечание</h3>
        </div>
        <div class="box-body">
            <p>
                Внимание! В файле импорта первая строка должна содержать заголовок. Наименования полей стандартизированы, они указаны в скобках, только латинские символы. Обратите внимание, что сопоставление объектов осуществляется с учетом регистра: &laquo;Директор&raquo; и &laquo;директор&raquo; - разные данные.
            </p>
            <p><strong>Обратите также внимание</strong>, что файл импорта, который Вы предоставляете, должен содержать только один лист в книге. В противном случае импорт не может быть выполнен.</p>
            <p>
                Файл импорта должен содержать следующие поля (порядок может быть любой): <strong>Номер *</strong> (id), <strong>Дата *</strong> (date), <strong>Код ФККО-2014 *</strong> (fkko), <strong>Наименование *</strong> (name).
            </p>
        </div>
    </div>
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'importFile')->fileInput() ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'type')->widget(Select2::className(), [
                'data' => ProductsImport::arrayMapTypesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Номенклатура', ['/products'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= Html::submitButton('<i class="fa fa-cloud-upload" aria-hidden="true"></i> Выполнить', ['class' => 'btn btn-success btn-lg']) ?>

    </div>
    <?php ActiveForm::end() ?>

</div>