<?php

use yii\db\Migration;

/**
 * Создается таблица "Свойства, которыми возможно описать статьи расходов".
 */
class m190518_201718_create_po_properties_table extends Migration
{
    /**
     * Наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'po_properties';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Свойства, которыми возможно описать статьи расходов"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->notNull()->comment('Наименование'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
