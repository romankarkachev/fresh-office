<?php

use yii\db\Migration;

/**
 * Создается таблица "Типы перевозчиков".
 */
class m170603_165947_create_ferrymen_types_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Типы перевозчиков"';
        };

        $this->createTable('ferrymen_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('ferrymen_types', [
            'id' => 1,
            'name' => 'Разовый',
        ]);

        $this->insert('ferrymen_types', [
            'id' => 2,
            'name' => 'Периодический',
        ]);

        $this->insert('ferrymen_types', [
            'id' => 3,
            'name' => 'Постоянный',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('ferrymen_types');
    }
}
