<?php

use yii\db\Migration;

/**
 * В таблицу проектов по экологии добавляется поле "Сумма договора".
 */
class m181008_104533_adding_contract_amount_to_eco_projects extends Migration
{
    public function up()
    {
        $this->addColumn('eco_projects', 'contract_amount', $this->decimal(12, 2)->comment('Сумма') . ' AFTER `ca_id`');
    }

    public function down()
    {
        $this->dropColumn('eco_projects', 'contract_amount');
    }
}
