<?php

use yii\db\Migration;

/**
 * Создается таблица "Типы почтовых ящиков".
 */
class m180524_164601_create_ce_mailboxes_types_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Типы почтовых ящиков"';
        };

        $this->createTable('ce_mailboxes_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('ce_mailboxes_types', [
            'id' => 1,
            'name' => 'Сторонний',
        ]);

        $this->insert('ce_mailboxes_types', [
            'id' => 2,
            'name' => 'Корпоративный',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('ce_mailboxes_types');
    }
}
