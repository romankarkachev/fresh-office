<?php

use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\OrganizationsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Организации | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Организации';
?>
<div class="organizations-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            'name_short',
            'inn',
            'kpp',
            'ogrn',
            'dir_name',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
