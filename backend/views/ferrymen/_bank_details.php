<?php

use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $dpBankDetails common\models\FerrymenBankDetails[] */
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <h4>Банковские счета  <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить счет', ['/ferrymen/create-bank-account'], [
            'id' => 'btn-add-bank-account',
            'class' => 'btn btn-default',
            'data-params' => ['ferryman_id' => $model->id],
            'data-method' => 'post',
        ]) ?></h4>
    </div>
    <div class="panel-body">
        <div class="ferryman-bank-details">
            <?= GridView::widget([
                'dataProvider' => $dpBankDetails,
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'columns' => [
                    'name_full',
                    [
                        'header' => 'Регистрация',
                        'format' => 'raw',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\FerrymenBankDetails */
                            $result = [];
                            if ($model->inn != null) $result[] = '<strong>ИНН</strong> ' . $model->inn;
                            if ($model->kpp != null) $result[] = '<strong>КПП</strong> ' . $model->kpp;
                            if ($model->ogrn != null) $result[] = '<strong>ОГРН</strong> ' . $model->ogrn;

                            if (count($result) > 0)
                                return implode(', ', $result);
                            else
                                return '';
                        }
                    ],
                    [
                        'header' => 'Реквизиты',
                        'format' => 'raw',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\FerrymenBankDetails */
                            $result = [];

                            if ($model->bank_an != null) $result[] = '<strong>Счет №</strong> ' . $model->bank_an;
                            if ($model->bank_name != null) $result[] = 'в ' . $model->bank_name;
                            if ($model->bank_bik != null) $result[] = '<strong>БИК</strong> ' . $model->bank_bik;
                            if ($model->bank_ca != null) $result[] = '<strong>корр. счет</strong> ' . $model->bank_ca;

                            if (count($result) > 0)
                                return implode(', ', $result);
                            else
                                return '';
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Действия',
                        'template' => '{update} {delete}',
                        'buttons' => [
                            'update' => function ($url, $model) {
                                return Html::a('<i class="fa fa-pencil"></i>', ['/ferrymen-bank-details/update', 'id' => $model->id], [
                                    'title' => Yii::t('yii', 'Редактировать'),
                                    'class' => 'btn btn-xs btn-default',
                                ]);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('<i class="fa fa-trash-o"></i>', ['/ferrymen/delete-bank-account', 'id' => $model->id], [
                                    'class' => 'btn btn-xs btn-danger',
                                    'title' => Yii::t('yii', 'Удалить'),
                                    'aria-label' => Yii::t('yii', 'Delete'),
                                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    'data-method' => 'post',
                                    'data-pjax' => '0'
                                ]);
                            }
                        ],
                        'options' => ['width' => '80'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                ],
            ]); ?>

        </div>
    </div>
</div>
