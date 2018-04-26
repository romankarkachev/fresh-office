<?php

use yii\db\Migration;

/**
 * Создается таблица "Типы загрузок транспорта перевозчиков".
 */
class m180419_070456_create_load_types_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Типы загрузок транспорта перевозчиков"';
        };

        $this->createTable('load_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('load_types', [
            'id' => 1,
            'name' => 'Верхняя',
        ]);

        $this->insert('load_types', [
            'id' => 2,
            'name' => 'Боковая',
        ]);

        $this->insert('load_types', [
            'id' => 3,
            'name' => 'Задняя',
        ]);

        $this->insert('load_types', [
            'id' => 4,
            'name' => 'Гидроборт',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('load_types');
    }
}
