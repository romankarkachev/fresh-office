<?php

use yii\db\Migration;

/**
 * Создается таблица "Типы проектов".
 */
class m170730_114938_create_projects_types_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Типы проектов"';
        };

        $this->createTable('projects_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('projects_types', [
            'name' => 'Заказ предоплата',
            'id' => 3,
        ]);

        $this->insert('projects_types', [
            'name' => 'Заказ постоплата',
            'id' => 5,
        ]);

        $this->insert('projects_types', [
            'name' => 'Документы предоплата',
            'id' => 12,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('projects_types');
    }
}
