<?php

use yii\db\Migration;

/**
 * Добавляется поле "Сумма" в таблицу платежных ордеров.
 */
class m171222_094349_adding_amount_to_payment_orders extends Migration
{
    public function up()
    {
        $this->addColumn('payment_orders', 'amount', $this->decimal(12,2)->comment('Сумма') . ' AFTER `projects`');
    }

    public function down()
    {
        $this->dropColumn('payment_orders', 'amount');
    }
}
