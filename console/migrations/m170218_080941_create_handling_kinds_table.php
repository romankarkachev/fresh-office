<?php

use yii\db\Migration;

/**
 * Создается таблица "Виды обращения с отходами".
 */
class m170218_080941_create_handling_kinds_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Виды обращения с отходами"';
        }

        $this->createTable('handling_kinds', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->notNull()->comment('Наименование'),
            'is_active' => 'TINYINT(1) DEFAULT 1 NOT NULL COMMENT "0 - отключен, 1 - активен"',
        ], $tableOptions);

        $this->insert('handling_kinds', [
            'name' => 'Сбор',
        ]);

        $this->insert('handling_kinds', [
            'name' => 'Транспортирование',
        ]);

        $this->insert('handling_kinds', [
            'name' => 'Обработка',
        ]);

        $this->insert('handling_kinds', [
            'name' => 'Утилизация',
        ]);

        $this->insert('handling_kinds', [
            'name' => 'Обезвреживание',
        ]);

        $this->insert('handling_kinds', [
            'name' => 'Размещение',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('handling_kinds');
    }
}
