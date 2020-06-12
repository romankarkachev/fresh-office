<?php

use common\models\Edf;
use common\models\EdfStates;

/* @var $this yii\web\View */
/* @var $model common\models\DesktopWidgets */

$content = [];
foreach (Edf::find()->select([
    'state_id',
    'stateName' => EdfStates::tableName() . '.`name`',
    'totalCount' => 'COUNT(*)',
])->where(['state_id' => [
    EdfStates::STATE_НА_ПОДПИСИ_У_ЗАКАЗЧИКА,
    EdfStates::STATE_СОГЛАСОВАНИЕ,
    EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА,
    EdfStates::STATE_УТВЕРЖДЕНО,
]])->joinWith('state')->groupBy('state_id')->asArray()->all() as $measure) {
    $content[] = $measure['stateName'] . ': ' . $measure['totalCount'];
}

$content[] = 'Всего незавершенных: ' . Edf::find()->select([
    'totalCount' => 'COUNT(*)',
])->where(['between', 'state_id', EdfStates::STATE_ЗАЯВКА, EdfStates::STATE_ДОСТАВЛЕН])->scalar();
?>

<div class="col-md-3">
    <div class="panel with-nav-tabs panel-success">
        <div class="panel-heading"><?= $model->name ?></div>
        <div class="panel-body">
            <?= implode('<br/>', $content) ?>

        </div>
    </div>
</div>
