<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы этапов проектов по экологии".
 */
class m180929_194940_create_eco_projects_milestones_files_table extends Migration
{
    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_UPLOADED_BY_NAME = 'uploaded_by';

    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_PROJECT_MILESTIONE_ID_NAME = 'project_milestone_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля uploaded_by
     */
    private $fkUploadedByName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля project_milestone_id
     */
    private $fkProjectMilestoneIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'eco_projects_milestones_files';
        $this->fkUploadedByName = 'fk_' . $this->tableName . '_' . self::FIELD_UPLOADED_BY_NAME;
        $this->fkProjectMilestoneIdName = 'fk_' . $this->tableName . '_' . self::FIELD_PROJECT_MILESTIONE_ID_NAME;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы этапов проектов по экологии"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            self::FIELD_UPLOADED_BY_NAME => $this->integer()->notNull()->comment('Автор загрузки'),
            self::FIELD_PROJECT_MILESTIONE_ID_NAME => $this->integer()->notNull()->comment('Этап проекта по экологии'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_UPLOADED_BY_NAME, $this->tableName, self::FIELD_UPLOADED_BY_NAME);
        $this->createIndex(self::FIELD_PROJECT_MILESTIONE_ID_NAME, $this->tableName, self::FIELD_PROJECT_MILESTIONE_ID_NAME);

        $this->addForeignKey($this->fkUploadedByName, $this->tableName, self::FIELD_UPLOADED_BY_NAME, 'user', 'id');
        $this->addForeignKey($this->fkProjectMilestoneIdName, $this->tableName, self::FIELD_PROJECT_MILESTIONE_ID_NAME, 'eco_projects_milestones', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey($this->fkProjectMilestoneIdName, $this->tableName);
        $this->dropForeignKey($this->fkUploadedByName, $this->tableName);

        $this->dropIndex(self::FIELD_PROJECT_MILESTIONE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_UPLOADED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
