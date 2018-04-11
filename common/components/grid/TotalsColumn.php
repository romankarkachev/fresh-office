<?php

/**
 * Выполняет суммирование значения колонки для вывода в подвале таблицы в качестве итога.
 */

namespace common\components\grid;

use yii\grid\DataColumn;

class TotalsColumn extends DataColumn
{
    private $_total = 0;

    public function getDataCellValue($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        $this->_total += floatval($value);
        return $value;
    }

    protected function renderFooterCellContent()
    {
        return '<strong>' . $this->grid->formatter->format($this->_total, $this->format) . '</strong>';
    }
}
