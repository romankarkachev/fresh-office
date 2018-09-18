<?php

use yii\db\Migration;

/**
 * Для поля 'is_complete' таблицы 'ce_messages' добавляется индекс.
 */
class m180910_150044_adding_is_complete_index_to_ce_messages extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_ce_messages_mailbox_id', 'ce_messages');
        $this->dropIndex('mailbox_id', 'ce_messages');
        $this->createIndex('mailbox_id', 'ce_messages', ['mailbox_id', 'is_complete']);

        $this->addForeignKey('fk_ce_messages_mailbox_id', 'ce_messages', 'mailbox_id', 'ce_mailboxes', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_ce_messages_mailbox_id', 'ce_messages');
        $this->dropIndex('mailbox_id', 'ce_messages');
        $this->createIndex('mailbox_id', 'ce_messages', 'mailbox_id');

        $this->addForeignKey('fk_ce_messages_mailbox_id', 'ce_messages', 'mailbox_id', 'ce_mailboxes', 'id');
    }
}
