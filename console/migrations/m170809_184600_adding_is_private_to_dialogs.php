<?php

use yii\db\Migration;

/**
 * Добавляется признак приватности в диалоги. Приватные диалоги недоступны менеджерам.
 */
class m170809_184600_adding_is_private_to_dialogs extends Migration
{
    public function up()
    {
        $this->addColumn('transport_requests_dialogs', 'is_private', 'TINYINT(1) DEFAULT"0" COMMENT"Приватный (между логистом и руководителем)" AFTER `tr_id`');
    }

    public function down()
    {
        $this->dropColumn('transport_requests_dialogs', 'is_private');
    }
}
