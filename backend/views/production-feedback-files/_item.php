<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $item \common\models\ProductionFeedbackFiles */
/* @var $uploadDir string относительный путь к папке с изображениями */
/* @var $renderHeader boolean изменился ли идентификатор проект для визуальной группировки */

$icon = ' <i class="fa fa-check-circle text-success" aria-hidden="true" title="Груз соответствует документам"></i>';
$action = '';
if ($item->action == 1) {
    $action = ' (НЕ СООТВЕТСТВУЕТ)';
    $icon = ' <i class="fa fa-times-circle text-danger" aria-hidden="true" title="Груз документам не соответствует"></i>';
}
else
?>
<?php if ($renderHeader): ?>
<div class="clearfix"></div>
<h4>
    Проект <strong><?= $item->project_id ?></strong>
    <small><?= Html::a('<i class="fa fa-share-square-o" aria-hidden="true"></i>', ['/storage', (new \common\models\FileStorageSearch())->formName() => ['ca_id' => $item->ca_id]], ['title' => 'Открыть файлы этого контрагента в хранилище', 'target' => '_blank']); ?></small>,
    файлы отправлены <?= Yii::$app->formatter->asDate($item->uploaded_at, 'php:d.m.Y в H:i') ?> пользователем <?= $item->uploadedByName ?>
    <?= $icon ?>
</h4>
<div class="clearfix"></div>
<?php endif; ?>
    <?= Html::a(
        Html::img($uploadDir . $item->thumb_fn, [
            'height' => 120,
            'width' => 160,
        ]),
        $uploadDir . $item->fn, [
            'rel' => 'fancybox',
            'class' => 'img-thumbnail pull-left mr-5',
            'title' => $item->ofn . ' - Проект ' . $item->project_id . ', отправлено ' . Yii::$app->formatter->asDate($item->uploaded_at, 'php:d.m.Y в H:i') . $action,
        ]
    ); ?>
