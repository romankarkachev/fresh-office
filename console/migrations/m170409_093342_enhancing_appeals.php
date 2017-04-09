<?php

use yii\db\Migration;

/**
 * Добавляются колонки в таблицу обращений.
 */
class m170409_093342_enhancing_appeals extends Migration
{
    public function up()
    {
        $this->addColumn('appeals', 'request_referrer', $this->string()->comment('Поле post-запроса Referer'));
        $this->addColumn('appeals', 'request_user_agent', $this->string()->comment('Поле post-запроса userAgent'));
        $this->addColumn('appeals', 'request_user_ip', $this->string(30)->comment('IP отправителя'));
    }

    public function down()
    {
        $this->dropColumn('appeals', 'request_user_ip');
        $this->dropColumn('appeals', 'request_user_agent');
        $this->dropColumn('appeals', 'request_referrer');
    }
}
