<?php

use yii\db\Migration;

/**
 * Создается таблица "Адреса писем корпоративной почты".
 */
class m180510_190204_create_ce_addresses_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Адреса писем корпоративной почты"';
        };

        $this->createTable('ce_addresses', [
            'id' => $this->primaryKey(),
            'message_id' => $this->integer()->notNull()->comment('Письмо'),
            'type' => $this->string(10)->notNull()->comment('Тип'),
            'email' => $this->string()->notNull()->comment('Адрес'),
            'name' => $this->string()->comment('Имя отправителя'),
        ], $tableOptions);

        $this->createIndex('message_id', 'ce_addresses', 'message_id');

        $this->addForeignKey('fk_ce_addresses_message_id', 'ce_addresses', 'message_id', 'ce_messages', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_ce_addresses_message_id', 'ce_addresses');

        $this->dropIndex('message_id', 'ce_addresses');

        $this->dropTable('ce_addresses');
    }
}
