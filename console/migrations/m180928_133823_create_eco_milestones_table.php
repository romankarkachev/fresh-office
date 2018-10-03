<?php

use yii\db\Migration;

/**
 * Создается таблица "Этапы выполнения проектов".
 */
class m180928_133823_create_eco_milestones_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'eco_milestones';

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Этапы выполнения проектов"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->comment('Наименование'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable($this->tableName);
    }
}
