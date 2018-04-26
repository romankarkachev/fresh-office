<?php

use ferryman\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FerrymenBankDetailsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Банковские счета | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Банковские счета';

$this->params['breadcrumbsRight'][] = ['label' => 'Создать', 'icon' => 'fa fa-plus-circle fa-lg', 'url' => ['create'], 'class' => 'btn text-success'];
$this->params['breadcrumbsRight'][] = ['icon' => 'fa fa-sort-amount-asc', 'url' => ['/bank-accounts'], 'title' => 'Сбросить отбор и применить сортировку по-умолчанию'];
?>
<div class="bank-accounts-list">
    <div class="card">
        <div class="card-block">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'name_full',
                        'visible' => Yii::$app->user->can('root'),
                    ],
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
                        'class' => 'ferryman\components\grid\ActionColumn',
                        'visibleButtons' => [
                            'delete' => Yii::$app->user->can('root'),
                        ],
                    ],
                ],
            ]); ?>

        </div>
    </div>
</div>
