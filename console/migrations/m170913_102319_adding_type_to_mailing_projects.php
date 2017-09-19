<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Тип рассылки" в таблицу отправленных на почту проектов.
 * 1 - рассылка робота с кодовым названием Zapier, 2 - рассылка проектов, которые переводятся в PDF и отправляются.
 */
class m170913_102319_adding_type_to_mailing_projects extends Migration
{
    public function up()
    {
        $this->addColumn('mailing_projects', 'type', 'TINYINT(1) DEFAULT "1" COMMENT "Тип рассылки" AFTER `sent_at`');
    }

    public function down()
    {
        $this->dropColumn('mailing_projects', 'type');
    }
}
