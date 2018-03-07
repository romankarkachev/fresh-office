<?php

use yii\db\Migration;

/**
 * Добавляется поле "Признак необходимости отправлять уведомление менеджеру, когда для него создается пакет
 * корреспонденции" в профиль пользователя.
 */
class m180222_173915_adding_notify_when_cp_to_profile extends Migration
{
    public function up()
    {
        $this->addColumn('profile', 'notify_when_cp', 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Признак необходимости отправлять уведомление менеджеру, когда для него создается пакет корреспонденции"');
    }

    public function down()
    {
        $this->dropColumn('profile', 'notify_when_cp');
    }
}
