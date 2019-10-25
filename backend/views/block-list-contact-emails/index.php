<?php

use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CpBlContactEmailsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'E-mail\'ы отписанных от рассылки | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'E-mail\'ы отписанных от рассылки';
?>
<div class="cp-bl-contact-emails-list">
    <p>
        Список содержит электронных ящиков контактных лиц контрагентов, отказавшихся от получения уведомлений о состоянии
        почтовых отправлений.
    </p>
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'fo_ca_id',
            'email:email',
            [
                'class' => 'backend\components\grid\ActionColumn',
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
            ],
        ],
    ]); ?>

</div>
