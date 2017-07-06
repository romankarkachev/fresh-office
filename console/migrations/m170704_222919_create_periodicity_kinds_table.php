<?php

use yii\db\Migration;

/**
 * Создается таблица "Виды периодичности сотрудничества".
 */
class m170704_222919_create_periodicity_kinds_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Виды периодичности сотрудничества"';
        };

        $this->createTable('periodicity_kinds', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->comment('Наименование'),
        ], $tableOptions);

        $this->insert('periodicity_kinds', [
            'id' => 1,
            'name' => 'Разовая',
        ]);

        $this->insert('periodicity_kinds', [
            'id' => 2,
            'name' => 'Регулярная',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('periodicity_kinds');
    }
}
