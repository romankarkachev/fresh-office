<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model array массив с данными проекта */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-condensed">
                <thead>
                <tr>
                    <th>Контрагент</th>
                    <th>Тип проекта</th>
                    <th>Контактное лицо</th>
                    <th>Дата вывоза</th>
                    <th>Ответственный</th>
                    <th>Перевозчик</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?= $model['ca_name'] ?></td>
                    <td><?= $model['type_name'] ?></td>
                    <td><?= $model['contact_name'] ?> <?= $model['contact_phone'] ?></td>
                    <td><?= Yii::$app->formatter->asDate($model['vivozdate'], 'php:d.m.Y') ?></td>
                    <td><?= $model['manager_name'] ?></td>
                    <td><?= $model['ferryman'] ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php if ($model['comment'] != null): ?>
        <div class="well well-small">
            <?= nl2br($model['comment']) ?>

        </div>
        <?php endif; ?>

        <?php if ($model['properties'] != null): ?>
        <div class="row">
            <div class="col-md-7 col-lg-6">
                <h4 class="text-center">Параметры проекта</h4>
                <table class="table table-bordered">
                <?php
                    foreach($model['properties'] as $property) {
                    if (strpos($property['property'], 'Оплата ТС') !== false) continue;
                ?>
                    <tr>
                        <td class="active"><strong><?= $property['property'] ?></strong></td>
                        <td><?= $property['value'] ?></td>
                    </tr>
                <?php } ?>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($model['tp'] != null): ?>
        <div class="form-group">
            <h4 class="text-center">Товары и услуги</h4>
            <table class="table table-bordered">
            <?php foreach($model['tp'] as $property): ?>
                <tr>
                    <td><strong><?= $property['property'] ?></strong></td>
                    <td><?= $property['value'] . ' ' . $property['ED_IZM_TOVAR'] ?></td>
                </tr>
            <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<div id="block-documents_match">
    <label class="control-label" for="productionclosingprojects-documents_match">Груз соответствует документам?</label>
    <div>
        <?= Html::radioList('ProductionClosingProjects[documents_match]', null, ArrayHelper::map([
            [
                'id' => 0,
                'name' => '<i class="fa fa-thumbs-down" aria-hidden="true"></i> Груз документам не соответствует',
            ],
            [
                'id' => 1,
                'name' => '<i class="fa fa-thumbs-up" aria-hidden="true"></i> Груз соответствует документам',
            ],
        ], 'id', 'name'), [
            'id' => 'productionclosingprojects-documents_match',
            'class' => 'btn-group',
            'data-toggle' => 'buttons',
            'unselect' => null,
            'item' => function ($index, $label, $name, $checked, $value) {
                switch ($value) {
                    case 0:
                        return '<label class="btn btn-danger btn-lg' . ($checked ? ' active' : '') . '">' .
                            Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn', 'id' => 'productionclosingprojects-is_match']) . $label . '</label>';
                        break;
                    case 1:
                        return '<label class="btn btn-success btn-lg' . ($checked ? ' active' : '') . '">' .
                            Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn', 'id' => 'productionclosingprojects-mismatch']) . $label . '</label>';
                        break;
                }
            },
        ]) ?>

    </div>
</div>
