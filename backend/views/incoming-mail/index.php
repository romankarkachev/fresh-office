<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\IncomingMailController;
use common\models\IncomingMail;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IncomingMailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = IncomingMailController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = IncomingMailController::ROOT_LABEL;
?>
<div class="incoming-mail-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            'createdByProfileName:ntext:Автор',
            'inc_num:ntext:Вх. №',
            [
                'attribute' => 'inc_date',
                'label' => 'Получен',
                'format' => ['datetime', 'dd.MM.YYYY'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '90'],
            ],
            'typeName:ntext:Тип письма',
            [
                'attribute' => 'organizationName',
                'label' => 'Получатель',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\IncomingMail */
                    /* @var $column \yii\grid\DataColumn */

                    $receiverProfileName = '';
                    if (!empty($model->receiverProfileName)) {
                        $receiverProfileName = '<br />' . $model->receiverProfileName;
                    }

                    return $model->{$column->attribute} . $receiverProfileName;
                },
            ],
            'description:ntext',
            //'date_complete_before',
            //'ca_src',
            //'ca_id',
            [
                'attribute' => 'ca_name',
                'label' => 'Отправитель',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\IncomingMail */
                    /* @var $column \yii\grid\DataColumn */

                    $addon = '';
                    switch ($model->ca_src) {
                        case IncomingMail::CA_SOURCES_КОНТРАГЕНТЫ:
                            break;
                        case IncomingMail::CA_SOURCES_ПЕРЕВОЗЧИКИ:
                            $addon = ' <i class="fa fa-truck text-muted" title="Это перевозчик"></i>';
                            break;
                        case IncomingMail::CA_SOURCES_FRESH_OFFICE:
                            $addon = ' ' . Html::img(\yii\helpers\Url::to(['/images/freshoffice16.png']), ['title' => 'Это контрагент из CRM Fresh Office']);
                            break;
                    }

                    return $model->{$column->attribute} . $addon;
                },
            ],
            //'receiver_id',
            'comment:ntext',
            [
                'class' => 'backend\components\grid\ActionColumn',
                'visible' => !Yii::$app->user->can(\common\models\AuthItem::ROLE_LOGIST),
            ],
        ],
    ]); ?>

</div>
