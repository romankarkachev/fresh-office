<?php

use yii\db\Migration;

/**
 * Добавляется поле "Срок действия договора" в таблицу перевозчиков.
 */
class m180410_171448_adding_contract_expires_at_to_ferrymen extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen', 'contract_expires_at', $this->date()->comment('Срок действия договора') . ' AFTER `ati_code`');
    }

    public function down()
    {
        $this->dropColumn('ferrymen', 'contract_expires_at');
    }
}
