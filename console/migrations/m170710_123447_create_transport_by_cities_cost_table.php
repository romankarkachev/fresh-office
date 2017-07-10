<?php

use yii\db\Migration;

/**
 * Создается таблица "Стоимость транспорта по городам".
 */
class m170710_123447_create_transport_by_cities_cost_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Стоимость транспорта по городам"';
        };

        $this->createTable('transport_by_cities_cost', [
            'id' => $this->primaryKey(),
            'city_id' => $this->integer()->unsigned()->notNull()->comment('Город'),
            'tt_id' => $this->integer()->notNull()->comment('Тип техники'),
            'amount' => $this->decimal(12,2)->comment('Стоимость'),
        ], $tableOptions);

        $this->createIndex('city_id', 'transport_by_cities_cost', 'city_id');
        $this->createIndex('tt_id', 'transport_by_cities_cost', 'tt_id');

        $this->addForeignKey('fk_transport_by_cities_cost_city_id', 'transport_by_cities_cost', 'city_id', 'city', 'city_id');
        $this->addForeignKey('fk_transport_by_cities_cost_tt_id', 'transport_by_cities_cost', 'tt_id', 'transport_types', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_transport_by_cities_cost_tt_id', 'transport_by_cities_cost');
        $this->dropForeignKey('fk_transport_by_cities_cost_city_id', 'transport_by_cities_cost');

        $this->dropIndex('tt_id', 'transport_by_cities_cost');
        $this->dropIndex('city_id', 'transport_by_cities_cost');

        $this->dropTable('transport_by_cities_cost');
    }
}
