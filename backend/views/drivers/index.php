<?php

use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\DriversSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Водители | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Водители';
?>
<div class="drivers-list">
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
            [
                'attribute' => 'ferrymanName',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\Drivers */
                    /* @var $column \yii\grid\DataColumn */

                    return Html::a($model->{$column->attribute}, [
                        '/ferrymen-drivers', 'DriversSearch' => ['ferryman_id' => $model->ferryman_id]
                    ], [
                        'title' => 'Отобрать по этому перевозчику',
                    ]);
                },
            ],
            'stateName',
            [
                'attribute' => 'surname',
                'label' => 'ФИО',
                'value' => function ($model) {
                    /* @var $model \common\models\Drivers */
                    /* @var $column \yii\grid\DataColumn */

                    return $model->surname . ' ' . $model->name . ' ' . $model->patronymic;
                },
            ],
            [
                'header' => 'Паспорт',
                'value' => function ($model) {
                    /* @var $model \common\models\Drivers */
                    /* @var $column \yii\grid\DataColumn */

                    $result = '';
                    if ($model->pass_serie != null)
                        $result .= $model->pass_serie;
                    if ($model->pass_num != null)
                        $result .= ' № ' . $model->pass_num;

                    return $result;
                },
            ],
            'driver_license',
            [
                'attribute' => 'phone',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Drivers */
                    /* @var $column \yii\grid\DataColumn */

                    return \common\models\Drivers::normalizePhoneNumber($model->{$column->attribute});
                }
            ],
            [
                'attribute' => 'instrCount',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'visible' => false,
            ],
            [
                'attribute' => 'instrDetails',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Drivers */
                    /* @var $column \yii\grid\DataColumn */

                    return nl2br($model->{$column->attribute});
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{checkDriversLicense} {checkPassport} {instructings} {update} {delete}',
                'buttons' => [
                    // кнопка для перехода на сайт ГИБДД для проверки водительского удостоверения водителя
                    'checkDriversLicense' => function ($url, $model) {
                        /* @var $model \common\models\Drivers */
                        $driver_license = str_replace(chr(32), '', $model->driver_license);
                        $dl_issued_at = Yii::$app->formatter->asDate($model->dl_issued_at, 'php:d.m.Y');
                        return Html::a('<i class="fa fa-id-card-o"></i>', 'http://www.gibdd.ru/check/driver/#' . $driver_license . '+' . $dl_issued_at,
                            [
                                'title' => Yii::t('yii', 'Проверить водительское удостоверение на сайте ГИБДД'),
                                'class' => 'btn btn-xs btn-default',
                                'target' => '_blank',
                            ]);
                    },
                    // кнопка для перехода на сайт ФМС для проверки паспорта водителя
                    'checkPassport' => function ($url, $model) {
                        /* @var $model \common\models\Drivers */
                        return Html::a('<i class="fa fa-id-badge"></i>', 'http://services.fms.gov.ru/info-service.htm',
                            [
                                'title' => Yii::t('yii', 'Проверить паспорт водителя на сайте ФМС'),
                                'class' => 'btn btn-xs btn-success',
                                'data' => [
                                    'method' => 'post',
                                    'params' => [
                                        'sid' => 2000,
                                        'form_name' => 'form',
                                        'DOC_SERIE' => $model->pass_serie,
                                        'DOC_NUMBER' => $model->pass_num,
                                    ],
                                    'pjax' => false,
                                ],
                                'target' => '_blank',
                            ]);
                    },
                    'instructings' => function ($url, $model) {
                        // количество инструктажей
                        $instrCount = $model->instrCount == null || $model->instrCount == 0 ? '' : ' (' . $model->instrCount . ')';

                        return Html::a('<i class="fa fa-comments"></i>', ['ferrymen/drivers-instructings', 'id' => $model->id], ['title' => Yii::t('yii', 'Инструктажи') . $instrCount, 'class' => 'btn btn-xs btn-default']);
                    },
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
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>
</div>
