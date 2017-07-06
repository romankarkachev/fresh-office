<?php

use yii\db\Migration;

/**
 * Создается таблица "Агрегатные состояния".
 */
class m170704_222841_create_aggregate_states_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Агрегатные состояния"';
        };

        $this->createTable('aggregate_states', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->comment('Наименование'),
        ], $tableOptions);

        $this->insert('aggregate_states', [
            'id' => 1,
            'name' => 'Твердое',
        ]);

        $this->insert('aggregate_states', [
            'id' => 2,
            'name' => 'Жидкое',
        ]);

        $this->insert('aggregate_states', [
            'id' => 3,
            'name' => 'Шлам',
        ]);

        $this->insert('aggregate_states', [
            'id' => 4,
            'name' => 'Газ',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('aggregate_states');
    }
}
