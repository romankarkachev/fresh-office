<?php

use yii\db\Migration;

/**
 * Создается таблица "Статусы пакетов корреспондении".
 */
class m180120_155412_create_correspondence_packages_states_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Статусы пакетов корреспондении"';
        };

        $this->createTable('correspondence_packages_states', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('correspondence_packages_states', [
            'id' => 1,
            'name' => 'Черновик',
        ]);

        $this->insert('correspondence_packages_states', [
            'id' => 2,
            'name' => 'Согласование',
        ]);

        $this->insert('correspondence_packages_states', [
            'id' => 3,
            'name' => 'Утвержден',
        ]);

        $this->insert('correspondence_packages_states', [
            'id' => 4,
            'name' => 'Отказ',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('correspondence_packages_states');
    }
}
