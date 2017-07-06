<?php

use yii\db\Migration;

/**
 * Создание таблицы "Единицы измерения".
 */
class m170704_222928_create_units_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Единицы измерения"';
        };

        $this->createTable('units', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->comment('Наименование'),
        ], $tableOptions);

        $this->insert('units', [
            'id' => 1,
            'name' => 'м³',
        ]);

        $this->insert('units', [
            'id' => 2,
            'name' => 'т',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('units');
    }
}
