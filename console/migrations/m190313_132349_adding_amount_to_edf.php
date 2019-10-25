<?php

use yii\db\Migration;

/**
 * Добавляется поле "Сумма договора" в таблицу электронных документов.
 */
class m190313_132349_adding_amount_to_edf extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('edf', 'amount', $this->decimal(12,2)->comment('Сумма договора') . ' AFTER `doc_date_expires`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('edf', 'amount');
    }
}
