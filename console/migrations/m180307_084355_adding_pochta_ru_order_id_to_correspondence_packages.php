<?php

use yii\db\Migration;

/**
 * Добавляется поле "Номер заказа" в пакеты корреспонденции.
 */
class m180307_084355_adding_pochta_ru_order_id_to_correspondence_packages extends Migration
{
    public function up()
    {
        $this->addColumn('correspondence_packages', 'pochta_ru_order_id', $this->integer()->comment('Номер заказа (оправления) на Почте России') . ' AFTER `pd_id`');
    }

    public function down()
    {
        $this->dropColumn('correspondence_packages', 'pochta_ru_order_id');
    }
}
