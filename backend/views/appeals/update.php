<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Appeals */
/* @var $is_wizard bool|null */

$this->title = 'Обращение № ' . $model->id . ' (' . $model->form_username . ') ' . HtmlPurifier::process('&mdash; Обращения | ') . Yii::$app->name;
if (Yii::$app->user->can('root') || Yii::$app->user->can('role_report1')) {
    $this->params['breadcrumbs'][] = ['label' => 'Обращения', 'url' => ['/appeals']];
    $this->params['breadcrumbs'][] = '№ ' . $model->id;
}
else
    $this->params['breadcrumbs'][] = 'Обращение № ' . $model->id . ' (' . $model->appealStateName . ', клиент ' . $model->caStateName . ')';
?>
<div class="appeals-update">
    <?= $this->render('_form', [
        'model' => $model,
        'is_wizard' => $is_wizard,
    ]) ?>

</div>
