<?php

use yii\db\Migration;

/**
 * Создается таблица "Группы статей расходов в платежных ордерах" (payment orders expenditure items groups).
 */
class m190518_193506_create_po_eig_table extends Migration
{
    /**
     * Наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'po_eig';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Группы статей расходов в платежных ордерах"';
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
