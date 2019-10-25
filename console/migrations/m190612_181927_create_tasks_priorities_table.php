<?php

use yii\db\Migration;

/**
 * Создаетя таблица "Приоритеты задач".
 */
class m190612_181927_create_tasks_priorities_table extends Migration
{
    /**
     * Наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'tasks_priorities';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Приоритеты задач"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->notNull()->comment('Наименование'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
