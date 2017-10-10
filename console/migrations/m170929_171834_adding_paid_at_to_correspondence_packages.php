<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Дата и время оплаты" в таблицу пакетов корреспонденции.
 */
class m170929_171834_adding_paid_at_to_correspondence_packages extends Migration
{
    public function up()
    {
        $this->addColumn('correspondence_packages', 'paid_at', $this->integer()->comment('Дата и время оплаты') . ' AFTER `delivered_at`');
    }

    public function down()
    {
        $this->dropColumn('correspondence_packages', 'paid_at');
    }
}
