<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Статус пакета корреспонденции".
 */
class m180120_155435_adding_cp_state_id_to_correspondence_packages extends Migration
{
    public function up()
    {
        $this->addColumn('correspondence_packages', 'cps_id', $this->integer()->comment('Статус пакета корреспонденции') . ' AFTER `is_manual`');

        $this->createIndex('cps_id', 'correspondence_packages', 'cps_id');

        $this->addForeignKey('fk_correspondence_packages_cps_id', 'correspondence_packages', 'cps_id', 'correspondence_packages_states', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_correspondence_packages_cps_id', 'correspondence_packages');

        $this->dropIndex('cps_id', 'correspondence_packages');

        $this->dropColumn('correspondence_packages', 'cps_id');
    }
}
