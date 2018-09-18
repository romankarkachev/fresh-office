<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Категория" в таблицу почтовых ящиков.
 */
class m180524_164548_adding_category_id_to_ce_mailboxes extends Migration
{
    public function up()
    {
        $this->addColumn('ce_mailboxes', 'category_id', $this->integer()->comment('Категория') . ' AFTER `name`');

        $this->createIndex('category_id', 'ce_mailboxes', 'category_id');

        $this->addForeignKey('fk_ce_mailboxes_category_id', 'ce_mailboxes', 'category_id', 'ce_mailboxes_categories', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_ce_mailboxes_category_id', 'ce_mailboxes');

        $this->dropIndex('category_id', 'ce_mailboxes');

        $this->dropColumn('ce_mailboxes', 'category_id');
    }
}
