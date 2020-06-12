<?php

use yii\widgets\Pjax;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
?>
<div class="company-contact-persons">
    <?php Pjax::begin(['id' => 'pjax-contact-persons', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped'],
        'columns' => [
            'name:ntext:Контактное лицо',
            'phones:ntext:Телефоны',
            'emails:ntext:E-mail',
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
