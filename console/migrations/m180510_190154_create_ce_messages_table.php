<?php

use yii\db\Migration;

/**
 * Создается таблица "Письма корпоративной почты".
 */
class m180510_190154_create_ce_messages_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Письма корпоративной почты"';
        };

        $this->createTable('ce_messages', [
            'id' => $this->primaryKey(),
            'detected_at' => $this->integer()->notNull()->comment('Дата и время постановки в очередь письма'),
            'obtained_at' => $this->integer()->comment('Дата и время скачивания письма в систему'),
            'mailbox_id' => $this->integer()->notNull()->comment('Почтовый ящик'),
            'folder_tech' => $this->string()->comment('Папка (техническое название)'),
            'folder_name' => $this->string()->comment('Папка'),
            'uid' => $this->integer()->notNull()->comment('Уникальный идентификатор письма в почтовом ящике'),
            'subject' => $this->string()->comment('Тема письма'),
            'body_text' => $this->text()->comment('Текст письма в plain-виде'),
            'body_html' => $this->text()->comment('Текст письма в html-коде'),
            'attachment_count' => $this->integer()->defaultValue(0)->comment('Количество вложений письма'),
            'header' => $this->text()->comment('Технический заголовок письма'),
            'created_at' => $this->integer()->comment('Дата и время создания письма'),
            'is_complete' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Скачано ли письмо полностью"',
        ], $tableOptions);

        $this->createIndex('mailbox_id', 'ce_messages', 'mailbox_id');

        $this->addForeignKey('fk_ce_messages_mailbox_id', 'ce_messages', 'mailbox_id', 'ce_mailboxes', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_ce_messages_mailbox_id', 'ce_messages');

        $this->dropIndex('mailbox_id', 'ce_messages');

        $this->dropTable('ce_messages');
    }
}
