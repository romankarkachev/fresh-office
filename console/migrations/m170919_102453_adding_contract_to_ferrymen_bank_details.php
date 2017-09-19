<?php

use yii\db\Migration;

/**
 * Добавляются номер и дата договора для банковских реквизитов перевозчика.
 */
class m170919_102453_adding_contract_to_ferrymen_bank_details extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen_bank_details', 'contract_num', $this->string(30)->comment('Номер договора') . ' AFTER `bank_ca`');
        $this->addColumn('ferrymen_bank_details', 'contract_date', $this->date()->comment('Дата договора') . ' AFTER `contract_num`');
    }

    public function down()
    {
        $this->dropColumn('ferrymen_bank_details', 'contract_num');
        $this->dropColumn('ferrymen_bank_details', 'contract_date');
    }
}
