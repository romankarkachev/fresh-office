<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Дата и время доставки корреспонденции".
 */
class m170823_092131_adding_delivered_at_to_correspondence_packages extends Migration
{
    public function up()
    {
        $this->addColumn('correspondence_packages', 'delivered_at', $this->integer()->comment('Дата и время доставки') . ' AFTER `sent_at`');
    }

    public function down()
    {
        $this->dropColumn('correspondence_packages', 'delivered_at');
    }
}
