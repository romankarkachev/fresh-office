<?php

use backend\components\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model \common\models\PoAt */
?>
<div class="row">
    <div class="col-md-4">
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => Json::decode($model->properties),
            ]),
            'tableOptions' => ['class' => 'table table-bordered table-condensed'],
            'layout' => '{items}',
            'columns' => [
                'propertyName:ntext:Свойство',
                'valueName:ntext:Значение',
            ],
        ]); ?>

    </div>
</div>
