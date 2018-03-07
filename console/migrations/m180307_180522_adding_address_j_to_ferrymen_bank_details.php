<?php

use yii\db\Migration;

/**
 * Добавляются поля для ввода адресов в таблицу банковских счетов перевозчиков.
 */
class m180307_180522_adding_address_j_to_ferrymen_bank_details extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen_bank_details', 'address_j', $this->text()->comment('Адрес юридический') . ' AFTER `bank_ca`');
        $this->addColumn('ferrymen_bank_details', 'address_f', $this->text()->comment('Адрес фактический') . ' AFTER `address_j`');
    }

    public function down()
    {
        $this->dropColumn('ferrymen_bank_details', 'address_j');
        $this->dropColumn('ferrymen_bank_details', 'address_f');
    }
}
