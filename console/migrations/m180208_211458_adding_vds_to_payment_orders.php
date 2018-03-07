<?php

use yii\db\Migration;

/**
 * Добавляется поле "Даты вывоза" в таблицу платежных ордеров.
 */
class m180208_211458_adding_vds_to_payment_orders extends Migration
{
    public function up()
    {
        $this->addColumn('payment_orders', 'vds', $this->text()->comment('Даты вывоза из проектов') . ' AFTER `cas`');
    }

    public function down()
    {
        $this->dropColumn('payment_orders', 'vds');
    }

}
