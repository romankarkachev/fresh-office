<?php

use yii\db\Migration;

/**
 * Добавляется поле "Наименование перевозчика в CRM" в таблицу перевозчиков.
 */
class m180115_074123_adding_name_crm_to_ferrymen extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen', 'name_crm', $this->string()->comment('Наименование в CRM') . ' AFTER `name`');
    }

    public function down()
    {
        $this->dropColumn('ferrymen', 'name_crm');
    }
}
