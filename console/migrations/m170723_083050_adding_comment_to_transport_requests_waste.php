<?php

use yii\db\Migration;

/**
 * Добавляется поле "Примечание" в табличную часть запросов.
 */
class m170723_083050_adding_comment_to_transport_requests_waste extends Migration
{
    public function up()
    {
        $this->addColumn('transport_requests_waste', 'comment', $this->string(50)->comment('Примечание'));
    }

    public function down()
    {
        $this->dropColumn('transport_requests_waste', 'comment');
    }
}
