<?php

use yii\db\Migration;

/**
 * Добавляется поле, которое содержит флаг-отметку запроса избранным.
 */
class m170723_083032_adding_favorite_to_transport_requests extends Migration
{
    public function up()
    {
        $this->addColumn('transport_requests', 'is_favorite', 'TINYINT(1) DEFAULT"0" COMMENT"Избранный" AFTER `state_id`');
    }

    public function down()
    {
        $this->dropColumn('transport_requests', 'is_favorite');
    }
}
