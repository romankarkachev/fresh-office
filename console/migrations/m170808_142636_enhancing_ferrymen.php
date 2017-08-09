<?php

use yii\db\Migration;

/**
 * Добавляется таблица "ОПФХ", поля ОПФХ и "Плательщик НДС" в таблице перевозчиков.
 */
class m170808_142636_enhancing_ferrymen extends Migration
{
    public function up()
    {
        // опфх и плательшик ндс
        $table1Options = null;
        if ($this->db->driverName === 'mysql') {
            $table1Options = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Организационно-правовые формы хозяйствования"';
        };

        $this->createTable('opfh', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('Наименование'),
        ], $table1Options);

        $this->insert('opfh', [
            'id' => 1,
            'name' => 'Физлицо',
        ]);

        $this->insert('opfh', [
            'id' => 2,
            'name' => 'ИП',
        ]);

        $this->insert('opfh', [
            'id' => 3,
            'name' => 'ООО',
        ]);

        $this->addColumn('ferrymen', 'opfh_id', $this->integer()->comment('ОПФХ') . ' AFTER `name`');
        $this->createIndex('opfh_id', 'ferrymen', 'opfh_id');
        $this->addForeignKey('fk_ferrymen_opfh_id', 'ferrymen', 'opfh_id', 'opfh', 'id');

        $this->addColumn('ferrymen', 'tax_kind', 'TINYINT(1) COMMENT "Плательщик НДС (0 - нет, 1 - да)" AFTER `opfh_id`');

        // контактные данные диспетчера и руководителя
        $this->addColumn('ferrymen', 'post', $this->string(100)->comment('Должность'));

        $this->addColumn('ferrymen', 'phone_dir', $this->string(50)->comment('Телефоны'));
        $this->addColumn('ferrymen', 'email_dir', $this->string()->comment('E-mail'));
        $this->addColumn('ferrymen', 'contact_person_dir', $this->string(50)->comment('Контактное лицо'));
        $this->addColumn('ferrymen', 'post_dir', $this->string(100)->comment('Должность'));
    }

    public function down()
    {
        // опфх и плательшик ндс
        $this->dropColumn('ferrymen', 'tax_kind');

        $this->dropForeignKey('fk_ferrymen_opfh_id', 'ferrymen');
        $this->dropIndex('opfh_id', 'ferrymen');
        $this->dropColumn('ferrymen', 'opfh_id');

        $this->dropTable('opfh');

        // контактные данные диспетчера и руководителя
        $this->dropColumn('ferrymen', 'post');
        $this->dropColumn('ferrymen', 'phone_dir');
        $this->dropColumn('ferrymen', 'email_dir');
        $this->dropColumn('ferrymen', 'contact_person_dir');
        $this->dropColumn('ferrymen', 'post_dir');
    }
}
