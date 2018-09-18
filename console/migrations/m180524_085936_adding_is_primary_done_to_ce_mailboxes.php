<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Первичный сбор завершен" в таблицу почтовых ящиков.
 */
class m180524_085936_adding_is_primary_done_to_ce_mailboxes extends Migration
{
    public function up()
    {
        $this->addColumn('ce_mailboxes', 'is_primary_done', 'TINYINT(1) DEFAULT"0" NOT NULL COMMENT"Первичный сбор завершен"');
    }

    public function down()
    {
        $this->dropColumn('ce_mailboxes', 'is_primary_done');
    }
}
