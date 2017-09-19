<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FerrymenSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Перевозчики | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Перевозчики';
?>
<div class="ferrymen-list">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => "<div style=\"position: relative; min-height: 20px;\"><small class=\"pull-right form-text text-muted\" style=\"position: absolute; bottom: 0; right: 0;\">{summary}</small></div>\n{items}\n{pager}",
        'summary' => "Показаны записи с <strong>{begin}</strong> по <strong>{end}</strong>, на странице <strong>{count}</strong>, всего <strong>{totalCount}</strong>. Страница <strong>{page}</strong> из <strong>{pageCount}</strong>.",
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'name',
            [
                'attribute' => 'driversCount',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'transportCount',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            'ftName',
            'pcName',
            'stateName',
            [
                'attribute' => 'contact_person',
                'label' => 'Диспетчер',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Ferrymen */
                    $result = '';
                    if ($model->contact_person != null) {
                        if ($result != '') $result .= '<br />';
                        $result .= $model->contact_person;
                        if ($model->post != null) {
                            $result .= ' (' . $model->post . ')';
                        }
                    }

                    if ($model->phone != null) {
                        if ($result != '') $result .= '<br />';
                        $result .= \common\models\Ferrymen::normalizePhone($model->phone);
                    }

                    if ($model->email != null) {
                        if ($result != '') $result .= '<br />';
                        $result .= $model->email;
                    }

                    return $result;
                }
            ],
            [
                'attribute' => 'contact_person_dir',
                'label' => 'Руководитель',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Ferrymen */
                    $result = '';
                    if ($model->contact_person_dir != null) {
                        if ($result != '') $result .= '<br />';
                        $result .= $model->contact_person_dir;
                        if ($model->post_dir != null) {
                            $result .= ' (' . $model->post_dir . ')';
                        }
                    }

                    if ($model->phone_dir != null) {
                        if ($result != '') $result .= '<br />';
                        $result .= \common\models\Ferrymen::normalizePhone($model->phone_dir);
                    }

                    if ($model->email_dir != null) {
                        if ($result != '') $result .= '<br />';
                        $result .= $model->email_dir;
                    }

                    return $result;
                }
            ],
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
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
