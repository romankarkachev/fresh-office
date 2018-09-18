<?php

use yii\db\Migration;

/**
 * Создается таблица "Почтовые ящики для корпортативного пользования".
 */
class m180510_132312_create_ce_mailboxes_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Почтовые ящики для корпортативного пользования"';
        };

        $this->createTable('ce_mailboxes', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'created_by' => $this->integer()->comment('Создатель'),
            'name' => $this->string()->comment('Наименование'),
            'is_active' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Признак активности ящика (0 - сбор писем не производится, 1 - производится)"',
            'host' => $this->string()->comment('Хост'),
            'username' => $this->string()->comment('Имя пользователя'),
            'password' => $this->string(50)->comment('Пароль'),
            'port' => $this->string(6)->comment('Порт'),
            'is_ssl' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Применят ли ssl"',
        ], $tableOptions);

        $this->createIndex('created_by', 'ce_mailboxes', 'created_by');

        $this->addForeignKey('fk_ce_mailboxes_created_by', 'ce_mailboxes', 'created_by', 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_ce_mailboxes_created_by', 'ce_mailboxes');

        $this->dropIndex('created_by', 'ce_mailboxes');

        $this->dropTable('ce_mailboxes');
    }
}
