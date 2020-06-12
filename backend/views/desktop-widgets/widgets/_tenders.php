<?php

use yii\helpers\ArrayHelper;
use common\models\Tenders;
use common\models\TendersStates;

/* @var $this yii\web\View */
/* @var $model common\models\DesktopWidgets */

$totalCount = 0;
$content = [];
$tenders = Tenders::find()->select([
    'state_id',
    'stateName' => TendersStates::tableName() . '.`name`',
    'tendersCount' => 'COUNT(*)',
])->where(['state_id' => [
    TendersStates::STATE_СОГЛАСОВАНА,
    TendersStates::STATE_В_РАБОТЕ,
    TendersStates::STATE_ЗАЯВКА_ПОДАНА,
    TendersStates::STATE_СОГЛАСОВАНИЕ_РОП,
    TendersStates::STATE_СОГЛАСОВАНИЕ_РУКОВОДСТВОМ,
]])->joinWith('state')->groupBy('state_id')->asArray()->all();
foreach ($tenders as $measure) {
    if (ArrayHelper::isIn($measure['state_id'], [
        TendersStates::STATE_СОГЛАСОВАНИЕ_РОП,
        TendersStates::STATE_СОГЛАСОВАНИЕ_РУКОВОДСТВОМ,
    ])) {
        // такие показатели суммируются под одним именем
        $totalCount += $measure['tendersCount'];
        continue;
    }
    $content[] = $measure['stateName'] . ': ' . $measure['tendersCount'];
}

if (!empty($totalCount)) {
    $content[] = 'Согласование: ' . $totalCount;
}
?>

<div class="col-md-3">
    <div class="panel with-nav-tabs panel-success">
        <div class="panel-heading"><?= $model->name ?></div>
        <div class="panel-body">
            <?= implode('<br/>', $content) ?>

        </div>
    </div>
</div>
