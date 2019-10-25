<?php

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportPoAnalytics */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $columns array колонки для таблицы */

$this->title = 'Аналитика платежных ордеров по бюджету | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Аналитика платежных ордеров по бюджету';
?>
<div class="reports-po-analytics">
    <?= $this->render('_search_po', ['model' => $searchModel]); ?>

    <div class="table-responsive">
        <?php if (is_array($columns) && count($columns) > 0): ?>
        <?php $summaryCurrent = $summary = []; for ($i = 1; $i <= 12; $i++) {$summaryCurrent['total' . $i] = 0; $summary['total' . $i] = 0;} ?>
        <table id="gw-po-analytics" class="table table-bordered table-striped table-hover">
            <colgroup>
                <col>
                <?php foreach ($columns as $column): ?>
                <col width="110">
                <?php endforeach; ?>
            </colgroup>
            <thead>
                <tr>
                    <td><?= $columns['name'] ?></td>
                    <?php foreach ($columns as $column): ?>
                    <?php if (is_array($column)): ?>
                    <th class="<?= $column['headerOptions']['class'] ?>"><?= isset($column['label']) ? $column['label'] : $searchModel->getAttributeLabel($column)?></th>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <?php if (is_array($dataProvider) && count($dataProvider)): ?>
            <?php $currentGroup = -1; ?>
            <tbody>
            <?php foreach ($dataProvider as $index => $row): ?>
            <?php if ($currentGroup != $row['groupName']): ?>
            <?php if ($currentGroup != -1): ?>
            <tr>
                <td><strong>Итого <?= $currentGroup ?>:</strong></td>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <td class="text-bold text-right"><?= !empty($summaryCurrent['total' . $i]) ? (Yii::$app->formatter->asDecimal($summaryCurrent['total' . $i], fmod($summaryCurrent['total' . $i], 1) != 0 ? 2 : null)) : '&nbsp;' ?></td>
                <?php endfor; ?>
                <?php $summaryCurrent = []; for ($i = 1; $i <= 12; $i++) {$summaryCurrent['total' . $i] = 0;} ?>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="13"><strong><?= $row['groupName'] ?></strong></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td><?= $row['name'] ?></td>
                <?php foreach ($columns as $column): ?>
                <?php if (is_array($column)): ?>
                <?php $fieldName = intval(str_replace('amount', '', $column['attribute'])); $summaryCurrent['total' . $fieldName] += $row[$column['attribute']]; $summary['total' . $fieldName] += $row[$column['attribute']]; ?>
                <td class="<?= $column['contentOptions']['class'] ?>"><?= Yii::$app->formatter->asDecimal($row[$column['attribute']], fmod($row[$column['attribute']], 1) != 0 ? 2: null) ?></td>
                <?php endif; ?>
                <?php endforeach; ?>
            </tr>
            <?php $currentGroup = $row['groupName']; ?>
            <?php endforeach; ?>
            <tr>
                <td><strong>Итого <?= $currentGroup ?>:</strong></td>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <td class="text-bold text-right"><?= !empty($summaryCurrent['total' . $i]) ? Yii::$app->formatter->asDecimal($summaryCurrent['total' . $i], fmod($summaryCurrent['total' . $i], 1) != 0 ? 2 : null) : '&nbsp;' ?></td>
                <?php endfor; ?>
            </tr>
            <tr>
                <td><strong>Итого:</strong></td>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <td class="text-bold text-right"><?= !empty($summary['total' . $i]) ? Yii::$app->formatter->asDecimal($summary['total' . $i], fmod($summary['total' . $i], 1) != 0 ? 2 : null) : '&nbsp;' ?></td>
                <?php endfor; ?>
            </tr>
            </tbody>
            <?php else: ?>
            <tr>
                <td>
                    Записей нет.
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
