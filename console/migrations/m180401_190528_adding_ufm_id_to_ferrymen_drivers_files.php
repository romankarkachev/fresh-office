<?php

use yii\db\Migration;

/**
 * Добавляется поле "Тип контента" для идентификации содержимого документа программным способом.
 */
class m180401_190528_adding_ufm_id_to_ferrymen_drivers_files extends Migration
{
    public function up()
    {
        $this->addColumn('drivers_files', 'ufm_id', $this->integer()->comment('Вид документа') . ' AFTER `driver_id`');
        $this->createIndex('ufm_id', 'drivers_files', 'ufm_id');
        $this->addForeignKey('fk_drivers_files_ufm_id', 'drivers_files', 'ufm_id', 'uploading_files_meanings', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_drivers_files_ufm_id', 'drivers_files');
        $this->dropIndex('ufm_id', 'drivers_files');
        $this->dropColumn('drivers_files', 'ufm_id');
    }
}
