<?php

use yii\db\Migration;

/**
 * Создается таблица, в которой будет храниться информация о доступе пользователей к почтовым ящикам.
 */
class m180517_194948_create_ce_users_access_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Доступ пользователей к почтовым ящикам"';
        };

        $this->createTable('ce_users_access', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('Пользователь'),
            'mailbox_id' => $this->integer()->notNull()->comment('Почтовый ящик'),
        ], $tableOptions);

        $this->createIndex('user_id', 'ce_users_access', 'user_id');
        $this->createIndex('mailbox_id', 'ce_users_access', 'mailbox_id');

        $this->addForeignKey('fk_ce_users_access_user_id', 'ce_users_access', 'user_id', 'user', 'id');
        $this->addForeignKey('fk_ce_users_access_mailbox_id', 'ce_users_access', 'mailbox_id', 'ce_mailboxes', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_ce_users_access_mailbox_id', 'ce_users_access');
        $this->dropForeignKey('fk_ce_users_access_user_id', 'ce_users_access');

        $this->dropIndex('mailbox_id', 'ce_users_access');
        $this->dropIndex('user_id', 'ce_users_access');

        $this->dropTable('ce_users_access');
    }
}
