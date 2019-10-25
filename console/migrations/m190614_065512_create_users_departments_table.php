<?php

use yii\db\Migration;

/**
 * Создается таблица "Отделы, к которым относятся пользователи".
 */
class m190614_065512_create_users_departments_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_USER_ID_NAME = 'user_id';
    const FIELD_DEPARTMENT_ID_NAME = 'department_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля user_id
     */
    private $fkUserIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля department_id
     */
    private $fkDepartmentIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'users_departments';
        $this->fkUserIdName = 'fk_' . $this->tableName . '_' . self::FIELD_USER_ID_NAME;
        $this->fkDepartmentIdName = 'fk_' . $this->tableName . '_' . self::FIELD_DEPARTMENT_ID_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Отделы, к которым относятся пользователи"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_USER_ID_NAME => $this->integer()->notNull()->comment('Пользователь'),
            self::FIELD_DEPARTMENT_ID_NAME => $this->integer()->notNull()->comment('Отдел'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_USER_ID_NAME, $this->tableName, self::FIELD_USER_ID_NAME);
        $this->createIndex(self::FIELD_DEPARTMENT_ID_NAME, $this->tableName, self::FIELD_DEPARTMENT_ID_NAME);

        $this->addForeignKey($this->fkUserIdName, $this->tableName, self::FIELD_USER_ID_NAME, 'user', 'id');
        $this->addForeignKey($this->fkDepartmentIdName, $this->tableName, self::FIELD_DEPARTMENT_ID_NAME, 'departments', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkDepartmentIdName, $this->tableName);
        $this->dropForeignKey($this->fkUserIdName, $this->tableName);

        $this->dropIndex(self::FIELD_DEPARTMENT_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_USER_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
