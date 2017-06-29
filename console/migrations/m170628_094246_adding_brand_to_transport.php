<?php

use yii\db\Migration;

/**
 * Добавляется поле "Марка транспорта" в таблицу "Транспорт".
 */
class m170628_094246_adding_brand_to_transport extends Migration
{
    public function up()
    {
        $this->addColumn('transport', 'brand_id', $this->integer()->comment('Марка') . ' AFTER `tt_id`');

        $this->createIndex('brand_id', 'transport', 'brand_id');

        $this->addForeignKey('fk_transport_brand_id', 'transport', 'brand_id', 'transport_brands', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_transport_brand_id', 'transport');

        $this->dropIndex('brand_id', 'transport');

        $this->dropColumn('transport', 'brand_id');
    }
}
