<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Тип почтового ящика".
 */
class m180524_164602_adding_type_to_ce_mailboxes extends Migration
{
    public function up()
    {
        $this->addColumn('ce_mailboxes', 'type_id', $this->integer()->notNull()->defaultValue(1) . ' AFTER `name`');

        $this->alterColumn('ce_mailboxes', 'type_id', $this->integer()->notNull()->comment('Тип'));

        $this->createIndex('type_id', 'ce_mailboxes', 'type_id');

        $this->addForeignKey('fk_ce_mailboxes_type_id', 'ce_mailboxes', 'type_id', 'ce_mailboxes_types', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_ce_mailboxes_type_id', 'ce_mailboxes');

        $this->dropIndex('type_id', 'ce_mailboxes');

        $this->dropColumn('ce_mailboxes', 'type_id');
    }
}
