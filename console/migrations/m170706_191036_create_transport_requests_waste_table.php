<?php

use yii\db\Migration;

/**
 * Создается таблица "Табличная часть Отходы к запросам".
 */
class m170706_191036_create_transport_requests_waste_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Табличная часть Отходы к запросам"';
        };

        $this->createTable('transport_requests_waste', [
            'id' => $this->primaryKey(),
            'tr_id' => $this->integer()->notNull()->comment('Запрос на транспорт'),
            'fkko_id' => $this->integer()->comment('Код ФККО'),
            'fkko_name' => $this->string()->comment('ФККО'),
            'dc_id' => $this->integer()->comment('Класс опасности'),
            'packing_id' => $this->integer()->comment('Тип упаковки'),
            'ags_id' => $this->integer()->comment('Агрегатное состояние'),
            'unit_id' => $this->integer()->comment('Единица измерения'),
            'measure' => $this->decimal(12,2)->comment('Количество'),
        ], $tableOptions);

        $this->createIndex('tr_id', 'transport_requests_waste', 'tr_id');
        $this->createIndex('fkko_id', 'transport_requests_waste', 'fkko_id');
        $this->createIndex('dc_id', 'transport_requests_waste', 'dc_id');
        $this->createIndex('packing_id', 'transport_requests_waste', 'packing_id');
        $this->createIndex('ags_id', 'transport_requests_waste', 'ags_id');
        $this->createIndex('unit_id', 'transport_requests_waste', 'unit_id');

        $this->addForeignKey('fk_transport_requests_waste_tr_id', 'transport_requests_waste', 'tr_id', 'transport_requests', 'id');
        $this->addForeignKey('fk_transport_requests_waste_fkko_id', 'transport_requests_waste', 'fkko_id', 'fkko', 'id');
        $this->addForeignKey('fk_transport_requests_waste_dc_id', 'transport_requests_waste', 'dc_id', 'danger_classes', 'id');
        $this->addForeignKey('fk_transport_requests_waste_packing_id', 'transport_requests_waste', 'packing_id', 'packing_types', 'id');
        $this->addForeignKey('fk_transport_requests_waste_ags_id', 'transport_requests_waste', 'ags_id', 'aggregate_states', 'id');
        $this->addForeignKey('fk_transport_requests_waste_unit_id', 'transport_requests_waste', 'unit_id', 'units', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_transport_requests_waste_unit_id', 'transport_requests_waste');
        $this->dropForeignKey('fk_transport_requests_waste_ags_id', 'transport_requests_waste');
        $this->dropForeignKey('fk_transport_requests_waste_packing_id', 'transport_requests_waste');
        $this->dropForeignKey('fk_transport_requests_waste_dc_id', 'transport_requests_waste');
        $this->dropForeignKey('fk_transport_requests_waste_fkko_id', 'transport_requests_waste');
        $this->dropForeignKey('fk_transport_requests_waste_tr_id', 'transport_requests_waste');

        $this->dropIndex('unit_id', 'transport_requests_waste');
        $this->dropIndex('ags_id', 'transport_requests_waste');
        $this->dropIndex('packing_id', 'transport_requests_waste');
        $this->dropIndex('dc_id', 'transport_requests_waste');
        $this->dropIndex('fkko_id', 'transport_requests_waste');
        $this->dropIndex('tr_id', 'transport_requests_waste');

        $this->dropTable('transport_requests_waste');
    }
}
