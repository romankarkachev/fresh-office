<?php

use yii\db\Migration;

/**
 * Создается таблица "Классы опасности".
 */
class m170704_222741_create_danger_classes_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Классы опасности"';
        };

        $this->createTable('danger_classes', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->comment('Наименование'),
        ], $tableOptions);

        $this->insert('danger_classes', [
            'id' => 1,
            'name' => 'I класс',
        ]);

        $this->insert('danger_classes', [
            'id' => 2,
            'name' => 'II класс',
        ]);

        $this->insert('danger_classes', [
            'id' => 3,
            'name' => 'III класс',
        ]);

        $this->insert('danger_classes', [
            'id' => 4,
            'name' => 'IV класс',
        ]);

        $this->insert('danger_classes', [
            'id' => 5,
            'name' => 'V класс',
        ]);

        $this->insert('danger_classes', [
            'id' => 6,
            'name' => 'Не определен',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('danger_classes');
    }
}
