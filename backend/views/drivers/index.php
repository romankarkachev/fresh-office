<?php

use yii\helpers\Html;
use yii\grid\GridView;

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
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'ferrymanName',
            [
                'attribute' => 'surname',
                'label' => 'ФИО',
                'value' => function ($model) {
                    /* @var $model \common\models\Drivers */
                    return $model->surname . ' ' . $model->name . ' ' . $model->patronymic;
                },
            ],
            [
                'header' => 'Паспорт',
                'value' => function ($model) {
                    /* @var $model \common\models\Drivers */
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
                    return \common\models\Drivers::normalizePhoneNumber($model->{$column->attribute});
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
                        return Html::a('<i class="fa fa-comments"></i>', ['ferrymen/drivers-instructings', 'id' => $model->id], ['title' => Yii::t('yii', 'Инструктажи'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>
</div>