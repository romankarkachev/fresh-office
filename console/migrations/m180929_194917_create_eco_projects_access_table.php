<?php

use yii\db\Migration;

/**
 * Создается таблица "Доступ пользователей к проектам по экологии".
 */
class m180929_194917_create_eco_projects_access_table extends Migration
{
    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_PROJECT_ID_NAME = 'project_id';

    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_USER_ID_NAME = 'user_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля project_id
     */
    private $fkProjectIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля user_id
     */
    private $fkUserIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'eco_projects_access';
        $this->fkProjectIdName = 'fk_' . $this->tableName . '_' . self::FIELD_PROJECT_ID_NAME;
        $this->fkUserIdName = 'fk_' . $this->tableName . '_' . self::FIELD_USER_ID_NAME;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Доступ пользователей к проектам по экологии"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_PROJECT_ID_NAME => $this->integer()->notNull()->comment('Проект'),
            self::FIELD_USER_ID_NAME => $this->integer()->notNull()->comment('Пользователь, имеющий доступ к этому проекту'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_PROJECT_ID_NAME, $this->tableName, self::FIELD_PROJECT_ID_NAME);
        $this->createIndex(self::FIELD_USER_ID_NAME, $this->tableName, self::FIELD_USER_ID_NAME);

        $this->addForeignKey($this->fkProjectIdName, $this->tableName, self::FIELD_PROJECT_ID_NAME, 'eco_projects', 'id');
        $this->addForeignKey($this->fkUserIdName, $this->tableName, self::FIELD_USER_ID_NAME, 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey($this->fkUserIdName, $this->tableName);
        $this->dropForeignKey($this->fkProjectIdName, $this->tableName);

        $this->dropIndex(self::FIELD_USER_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_PROJECT_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
