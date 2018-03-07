<?php

use yii\db\Migration;

/**
 * Добавляется поле "Контактное лицо" в пакеты корреспонденции.
 */
class m180214_105248_adding_fo_contact_id_to_correspondence_packages extends Migration
{
    public function up()
    {
        $this->addColumn('correspondence_packages', 'fo_contact_id', $this->integer()->comment('Контактное лицо из CRM'));
        $this->addColumn('correspondence_packages', 'contact_person', $this->string(100)->comment('Контактное лицо'));
    }

    public function down()
    {
        $this->dropColumn('correspondence_packages', 'fo_contact_id');
        $this->dropColumn('correspondence_packages', 'contact_person');
    }
}
