<?php

use yii\db\Migration;

/**
 * Добавляется поле "Дата и время прочтения" в таблицу диалогов в запросе на транспорт.
 */
class m170723_112605_adding_read_at_to_transport_requests_dialogs extends Migration
{
    public function up()
    {
        $this->addColumn('transport_requests_dialogs', 'read_at', $this->integer()->comment('Прочитано'));
    }

    public function down()
    {
        $this->dropColumn('transport_requests_dialogs', 'read_at');
    }
}
