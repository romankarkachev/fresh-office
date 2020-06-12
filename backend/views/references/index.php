<?php

use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $items array массив элементов, который необходимо вывести на экран */

$this->title = 'Справочники | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Справочники';

$itemsCounter = -1;
$columnsCounter = 0;
?>
<div class="references-list form-group">
    <?php if (!empty($items)): ?>
    <div class="row">
    <?php foreach ($items as $item): ?>
    <?php if (!empty($item['header'])): ?>
        <div class="col-md-3">
            <a class="list-group-item active">
                <h4 class="list-group-item-heading"><?= $item['header'] ?></h4>
            </a>
            <div class="list-group">
                <?php foreach ($item['items'] as $subItem): ?>
                <a href="<?= Url::to($subItem['url']) ?>" class="list-group-item">
                    <h4 class="list-group-item-heading"><?= $subItem['label'] ?></h4>
                    <p class="list-group-item-text"><?= !empty($subItem['description']) ? $subItem['description'] : 'Информация отсутствует' ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        </div>
        <div class="row">
            <?php foreach ($item['items'] as $subItem): ?>
            <?php if ($itemsCounter == -1 || $itemsCounter > 3): ?>
            <?php if ($itemsCounter != -1): ?>
                </div><!-- list-group -->
            </div><!-- col-md-2 -->
            <?php endif; ?>
            <?php $itemsCounter = 0; ?>
            <div class="col-md-3">
                <div class="list-group">
            <?php endif; ?>
                <a href="<?= Url::to($subItem['url']) ?>" class="list-group-item">
                    <h4 class="list-group-item-heading"><?= $subItem['label'] ?></h4>
                    <p class="list-group-item-text"><?= !empty($subItem['description']) ? $subItem['description'] : 'Информация отсутствует' ?></p>
                </a>
            <?php $itemsCounter++; ?>
            <?php endforeach; ?>
        </div><!-- row -->
    <?php endif; ?>
    <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="lead text-muted">Справочников нет.</p>
    <?php endif; ?>
</div>
