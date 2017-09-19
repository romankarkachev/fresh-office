<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Адрес почтовый" в таблицу пакетов корреспонденции.
 */
class m170917_210157_adding_address_id_to_correspondence_packages extends Migration
{
    public function up()
    {
        $this->addColumn('correspondence_packages', 'address_id', $this->integer()->comment('Адрес почтовый') . ' AFTER `track_num`');

        $this->createIndex('address_id', 'correspondence_packages', 'address_id');

        $this->addForeignKey('fk_correspondence_packages_address_id', 'correspondence_packages', 'address_id', 'counteragents_post_addresses', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_correspondence_packages_address_id', 'correspondence_packages');

        $this->dropIndex('address_id', 'correspondence_packages');

        $this->dropColumn('correspondence_packages', 'address_id');
    }
}
