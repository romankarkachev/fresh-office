<?php

use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\foProjectsSearch */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $columns array массив колонок */
/* @var $labels array массив заголовков колонок */
/* @var $searchApplied bool */
/* @var $queryString string */

$this->title = 'Матрица статусов проектов | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Матрица статусов проектов';
?>
<div class="projects-states-matrix-list">
    <?= $this->render('_search_states_matrix', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?= Html::a('<i class="fa fa-file-excel-o" aria-hidden="true"></i> Экспорт в Excel', '/projects/states-matrix?export=true' . $queryString, ['class' => 'btn btn-default pull-right']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'gw-projects',
        'columns' => $columns,
    ]); ?>

</div>
<?php
//$urlCreatePaymentOrderBySelection = Url::to(['/projects/create-order-by-selection']);

$this->registerJs(<<<JS
$("input[type='checkbox'").iCheck({checkboxClass: "icheckbox_square-green"});
JS
, \yii\web\View::POS_READY);
?>
