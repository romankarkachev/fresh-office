<?php

use yii\db\Migration;

/**
 * Добавляется поле "Тип контента" для идентификации содержимого документа программным способом.
 */
class m180401_190537_adding_ufm_id_to_ferrymen_transport_files extends Migration
{
    public function up()
    {
        $this->addColumn('transport_files', 'ufm_id', $this->integer()->comment('Вид документа') . ' AFTER `transport_id`');
        $this->createIndex('ufm_id', 'transport_files', 'ufm_id');
        $this->addForeignKey('fk_transport_files_ufm_id', 'transport_files', 'ufm_id', 'uploading_files_meanings', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_transport_files_ufm_id', 'transport_files');
        $this->dropIndex('ufm_id', 'transport_files');
        $this->dropColumn('transport_files', 'ufm_id');
    }
}
