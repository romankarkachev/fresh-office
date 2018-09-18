<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\HtmlPurifier;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\LicensesRequests */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $is_wizard bool */

$this->title = 'Запрос лицензии № ' . $model->id . HtmlPurifier::process(' &mdash; Запросы лицензий | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Запросы лицензий', 'url' => ['/licenses-requests']];
$this->params['breadcrumbs'][] = '№ ' . $model->id;

$url = ['licenses-requests/update', 'id' => $model->id];
if (isset($is_wizard) && $is_wizard === true) {
    $url['is_wizard'] = true;
}
$action = Url::to($url);
?>
<div class="licenses-requests-update">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY в HH:mm'],
            ],
            'createdByName',
            [
                'attribute'  => 'stateName',
                'format' => 'raw',
                'value'  => function ($model) {
                    /* @var $model common\models\LicensesRequests */

                    $addon = '';
                    if ($model->state_id == \common\models\LicensesRequestsStates::LICENSE_STATE_ОТКАЗ)
                        $addon = ' <small class="text-muted">' . $model->comment . '</small>';
                    return $model->stateName . $addon;
                },
            ],
            'ca_name',
            'ca_email:email',
        ],
    ]) ?>

    <?= $this->render('_fkko', ['dataProvider' => $dataProvider]) ?>

    <?php if ($model->state_id == \common\models\LicensesRequestsStates::LICENSE_STATE_НОВЫЙ): ?>

    <?php $form = ActiveForm::begin(['action' => $action]); ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите причину отказа']) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-check-circle-o" aria-hidden="true"></i> Разрешить', ['class' => 'btn btn-success btn-lg', 'name' => 'allow']) ?>

        <?= Html::submitButton('<i class="fa fa-times-circle" aria-hidden="true"></i> Отказать', ['class' => 'btn btn-danger btn-lg', 'name' => 'reject', 'value' => true]) ?>

    </div>

    <?php ActiveForm::end(); ?>

    <?php else: ?>
    <p>
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Запросы лицензий', ['/licenses-requests'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if (Yii::$app->user->can('root')): ?>
        <?= Html::a('<i class="fa fa-trash-o"></i> Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger btn-lg',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
            'title' => 'Удалить запрос лицензии',
        ]) ?>

        <?php endif; ?>
    </p>
    <?php endif; ?>
</div>
