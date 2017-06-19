<?php

use yii\db\Migration;

/**
 * Создается таблица "Виды технического состояния транспортных средств".
 */
class m170604_091146_create_technical_conditions_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Виды технического состояния транспортных средств"';
        };

        $this->createTable('technical_conditions', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('technical_conditions', [
            'name' => 'Удовлетв.',
        ]);

        $this->insert('technical_conditions', [
            'name' => 'Среднее',
        ]);

        $this->insert('technical_conditions', [
            'name' => 'Неудовл.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('technical_conditions');
    }
}
