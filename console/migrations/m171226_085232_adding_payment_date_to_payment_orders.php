<?php

use yii\db\Migration;

/**
 * Добавляется поле "Дата оплаты" в таблицу платежных ордеров.
 */
class m171226_085232_adding_payment_date_to_payment_orders extends Migration
{
    public function up()
    {
        $this->addColumn('payment_orders', 'payment_date', $this->date()->comment('Дата оплаты') . ' AFTER `pd_id`');
    }

    public function down()
    {
        $this->dropColumn('payment_orders', 'payment_date');
    }
}
