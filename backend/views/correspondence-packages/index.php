<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CorrespondencePackagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Корреспонденция | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Пакеты корреспонденции';
?>
<div class="correspondence-packages-list">
    <p>
        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            //'created_at',
            //'ready_at',
            //'sent_at',
            'fo_project_id',
            'customer_name',
            'stateName',
            'typeName',
            [
                'attribute' => 'pad',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\TransportRequests */
                    $result = '';

                    $pad = json_decode($model->{$column->attribute}, true);
                    if (is_array($pad))
                        foreach ($pad as $document)
                            if ($document['is_provided'] == false)
                                $result .= '<span class="text-muted">' . $document['name'] . '</span> ';
                            else
                                $result .= '<strong>' . $document['name'] . '</strong> ';
                            //$result .= '<span class="' . ($document['is_provided'] == false ? 'text-muted' : 'text-success') . '">' . $document['name'] . '</span> ';

                    return $result;
                },
            ],
            'pdName',
            //'track_num',
            //'other:ntext',
            //'comment:ntext',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
