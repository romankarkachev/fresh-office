<?php

use yii\db\Migration;

/**
 * Создается таблица "Типы проектов".
 */
class m180928_133735_create_eco_types_table extends Migration
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
        $this->tableName = 'eco_types';

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Типы проектов"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert($this->tableName, [
            'id' => 1,
            'name' => 'ПНООЛР',
        ]);

        $this->insert($this->tableName, [
            'id' => 2,
            'name' => 'ПДВ',
        ]);

        $this->insert($this->tableName, [
            'id' => 3,
            'name' => 'СЗЗ',
        ]);

        $this->insert($this->tableName, [
            'id' => 4,
            'name' => 'НДС',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable($this->tableName);
    }
}
