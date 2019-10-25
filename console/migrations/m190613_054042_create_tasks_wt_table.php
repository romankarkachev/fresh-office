<?php

use yii\db\Migration;

/**
 * Создание таблицы "Сроки выполнения задач".
 */
class m190613_054042_create_tasks_wt_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_TASK_ID_NAME = 'task_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля task_id
     */
    private $fkTaskIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'tasks_wt';
        $this->fkTaskIdName = 'fk_' . $this->tableName . '_' . self::FIELD_TASK_ID_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Сроки выполнения задач"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_TASK_ID_NAME => $this->integer()->notNull()->comment('Задача'),
            'start_at' => $this->integer()->comment('Начало'),
            'finish_at' => $this->integer()->comment('Завершение'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_TASK_ID_NAME, $this->tableName, self::FIELD_TASK_ID_NAME);

        $this->addForeignKey($this->fkTaskIdName, $this->tableName, self::FIELD_TASK_ID_NAME, 'tasks', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkTaskIdName, $this->tableName);

        $this->dropIndex(self::FIELD_TASK_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
