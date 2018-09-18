<?php

use yii\db\Migration;

/**
 * Создается таблица "Категории почтовых ящиков".
 */
class m180524_164520_create_ce_mailboxes_categories_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Категории почтовых ящиков"';
        };

        $this->createTable('ce_mailboxes_categories', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->notNull()->comment('Наименование'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('ce_mailboxes_categories');
    }
}
