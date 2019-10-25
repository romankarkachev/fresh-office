<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы задач".
 */
class m190613_060933_create_tasks_files_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_UPLOADED_BY_NAME = 'uploaded_by';
    const FIELD_TASK_ID_NAME = 'task_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля uploaded_by
     */
    private $fkUploadedByName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля task_id
     */
    private $fkTaskIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'tasks_files';
        $this->fkUploadedByName = 'fk_' . $this->tableName . '_' . self::FIELD_UPLOADED_BY_NAME;
        $this->fkTaskIdName = 'fk_' . $this->tableName . '_' . self::FIELD_TASK_ID_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы задач"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            self::FIELD_UPLOADED_BY_NAME => $this->integer()->notNull()->comment('Автор загрузки'),
            self::FIELD_TASK_ID_NAME => $this->integer()->notNull()->comment('Задача'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_UPLOADED_BY_NAME, $this->tableName, self::FIELD_UPLOADED_BY_NAME);
        $this->createIndex(self::FIELD_TASK_ID_NAME, $this->tableName, self::FIELD_TASK_ID_NAME);

        $this->addForeignKey($this->fkUploadedByName, $this->tableName, self::FIELD_UPLOADED_BY_NAME, 'user', 'id');
        $this->addForeignKey($this->fkTaskIdName, $this->tableName, self::FIELD_TASK_ID_NAME, 'tasks', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkTaskIdName, $this->tableName);
        $this->dropForeignKey($this->fkUploadedByName, $this->tableName);

        $this->dropIndex(self::FIELD_TASK_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_UPLOADED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
