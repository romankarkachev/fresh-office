<?php

use yii\db\Migration;

/**
 * Создается таблица "Задачи".
 */
class m190612_181941_create_tasks_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';
    const FIELD_TYPE_ID_NAME = 'type_id';
    const FIELD_STATE_ID_NAME = 'state_id';
    const FIELD_PRIORITY_ID_NAME = 'priority_id';
    const FIELD_RESPONSIBLE_ID_NAME = 'responsible_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля created_by
     */
    private $fkCreatedByName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля type_id
     */
    private $fkTypeIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля state_id
     */
    private $fkStateIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля priority_id
     */
    private $fkPriorityIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля responsible_id
     */
    private $fkResponsibleIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'tasks';
        $this->fkCreatedByName = 'fk_' . $this->tableName . '_' . self::FIELD_CREATED_BY_NAME;
        $this->fkTypeIdName = 'fk_' . $this->tableName . '_' . self::FIELD_TYPE_ID_NAME;
        $this->fkStateIdName = 'fk_' . $this->tableName . '_' . self::FIELD_STATE_ID_NAME;
        $this->fkPriorityIdName = 'fk_' . $this->tableName . '_' . self::FIELD_PRIORITY_ID_NAME;
        $this->fkResponsibleIdName = 'fk_' . $this->tableName . '_' . self::FIELD_RESPONSIBLE_ID_NAME;

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Задачи"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->notNull()->comment('Автор создания'),
            self::FIELD_TYPE_ID_NAME => $this->integer()->notNull()->comment('Тип'),
            self::FIELD_STATE_ID_NAME => $this->integer()->notNull()->comment('Статус'),
            self::FIELD_PRIORITY_ID_NAME => $this->integer()->notNull()->comment('Приоритет'),
            'start_at' => $this->integer()->comment('Начало'),
            'finish_at' => $this->integer()->comment('Завершение'),
            'fo_ca_id' => $this->integer()->comment('Контрагент'),
            'fo_ca_name' => $this->string()->comment('Контрагент'),
            'fo_cp_id' => $this->integer()->comment('Контактное лицо'),
            'fo_cp_name' => $this->string(150)->comment('Контактное лицо'),
            self::FIELD_RESPONSIBLE_ID_NAME => $this->integer()->comment('Исполнитель'),
            'project_id' => $this->integer()->comment('Проект'),
            'purpose' => $this->text()->comment('Цель'),
            'solution' => $this->text()->comment('Результат'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, $this->tableName, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_TYPE_ID_NAME, $this->tableName, self::FIELD_TYPE_ID_NAME);
        $this->createIndex(self::FIELD_STATE_ID_NAME, $this->tableName, self::FIELD_STATE_ID_NAME);
        $this->createIndex(self::FIELD_PRIORITY_ID_NAME, $this->tableName, self::FIELD_PRIORITY_ID_NAME);
        $this->createIndex(self::FIELD_RESPONSIBLE_ID_NAME, $this->tableName, self::FIELD_RESPONSIBLE_ID_NAME);

        $this->addForeignKey($this->fkCreatedByName, $this->tableName, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey($this->fkTypeIdName, $this->tableName, self::FIELD_TYPE_ID_NAME, 'tasks_types', 'id');
        $this->addForeignKey($this->fkStateIdName, $this->tableName, self::FIELD_STATE_ID_NAME, 'tasks_states', 'id');
        $this->addForeignKey($this->fkPriorityIdName, $this->tableName, self::FIELD_PRIORITY_ID_NAME, 'tasks_priorities', 'id');
        $this->addForeignKey($this->fkResponsibleIdName, $this->tableName, self::FIELD_RESPONSIBLE_ID_NAME, 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkResponsibleIdName, $this->tableName);
        $this->dropForeignKey($this->fkPriorityIdName, $this->tableName);
        $this->dropForeignKey($this->fkStateIdName, $this->tableName);
        $this->dropForeignKey($this->fkTypeIdName, $this->tableName);
        $this->dropForeignKey($this->fkCreatedByName, $this->tableName);

        $this->dropIndex(self::FIELD_RESPONSIBLE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_PRIORITY_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_STATE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_TYPE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
