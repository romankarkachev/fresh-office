<?php

/**
 * Выполняет суммирование значения колонки для вывода в подвале таблицы в качестве итога.
 */

namespace backend\components\grid;

class TotalAmountColumn extends \yii\grid\DataColumn
{
    private $_total = 0;

    public function getDataCellValue($model, $key, $index)
    {
        $originalValue = parent::getDataCellValue($model, $key, $index);
        if ($this->format == 'raw') {
            // требуется обработка значения
            $value = str_replace(' &#8381;', '', parent::getDataCellValue($model, $key, $index)); // убираем значок рубля
            $value = str_replace(',', '.', preg_replace("/[^,0-9]/", '', $value));
        }
        else {
            $value = $originalValue;
        }

        $this->_total += $value;
        return $originalValue;
    }

    protected function renderFooterCellContent()
    {
        return '<strong>' . \common\models\FinanceTransactions::getPrettyAmount($this->_total, 'html') . '</strong>';
        // variazione:
        //return str_replace(',00', '', \Yii::$app->formatter->asDecimal($this->_total, 2)) . ' &#8381;';
        //return '<strong>' . $this->grid->formatter->format($this->_total, $this->format) . '</strong>';
    }
}
