<?php

/* @var $this yii\web\View */
/* @var array $runtimeLog массив с результатми выполнения */

$this->title = 'Объединение карточек контрагентов во Fresh Office | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Объединение контрагентов';
?>
<div class="merge-customers-result">
    <div class="list-group">
        <?php foreach ($runtimeLog as $record): ?>
        <a href="#" class="list-group-item">
            <h4 class="list-group-item-heading"><?= $record['name'] . ' (id ' . $record['id'] . ')' ?></h4>
            <?php foreach ($record['actions'] as $details): ?>
            <p class="list-group-item-text"><?= $details ?></p>
            <?php endforeach; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
