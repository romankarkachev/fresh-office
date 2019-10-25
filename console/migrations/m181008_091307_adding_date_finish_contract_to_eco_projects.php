<?php

use yii\db\Migration;

/**
 * Добавляется поле "Дата завершения проекта по договору" в таблицу проектов по экологии.
 */
class m181008_091307_adding_date_finish_contract_to_eco_projects extends Migration
{
    public function up()
    {
        $this->addColumn('eco_projects', 'date_finish_contract', $this->date()->comment('Дата завершения проекта по договору') . ' AFTER `date_start`');
    }

    public function down()
    {
        $this->dropColumn('eco_projects', 'date_finish_contract');
    }
}
