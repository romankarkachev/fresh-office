<?php

use yii\db\Migration;

/**
 * Создается таблица "Этапы проектов по экологии".
 */
class m180929_194930_create_eco_projects_milestones_table extends Migration
{
    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_PROJECT_ID_NAME = 'project_id';

    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_MILESTONE_ID_NAME = 'milestone_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля project_id
     */
    private $fkProjectIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля milestone_id
     */
    private $fkMilestoneIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'eco_projects_milestones';
        $this->fkProjectIdName = 'fk_' . $this->tableName . '_' . self::FIELD_PROJECT_ID_NAME;
        $this->fkMilestoneIdName = 'fk_' . $this->tableName . '_' . self::FIELD_MILESTONE_ID_NAME;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Этапы проектов по экологии"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_PROJECT_ID_NAME => $this->integer()->notNull()->comment('Проект'),
            self::FIELD_MILESTONE_ID_NAME => $this->integer()->notNull()->comment('Этап'),
            'is_file_reqiured' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Требуется ли предоставление минимум одного файла для закрытия этапа"',
            'is_affects_to_cycle_time' => 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Влияет ли на расчет общей продолжительности для завершения проекта"',
            'time_to_complete_required' => $this->smallInteger(6)->comment('Время для завершения этапа в днях'),
            'order_no' => $this->integer()->notNull()->defaultValue(0)->comment('Номер по порядку'),
            'date_close_plan' => $this->date()->comment('Планируемая дата завершения проекта'),
            'closed_at' => $this->integer()->comment('Фактическая дата завершения проекта'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_PROJECT_ID_NAME, $this->tableName, self::FIELD_PROJECT_ID_NAME);
        $this->createIndex(self::FIELD_MILESTONE_ID_NAME, $this->tableName, self::FIELD_MILESTONE_ID_NAME);

        $this->addForeignKey($this->fkProjectIdName, $this->tableName, self::FIELD_PROJECT_ID_NAME, 'eco_projects', 'id');
        $this->addForeignKey($this->fkMilestoneIdName, $this->tableName, self::FIELD_MILESTONE_ID_NAME, 'eco_milestones', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey($this->fkMilestoneIdName, $this->tableName);
        $this->dropForeignKey($this->fkProjectIdName, $this->tableName);

        $this->dropIndex(self::FIELD_MILESTONE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_PROJECT_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
