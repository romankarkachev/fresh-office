<?php

use yii\db\Migration;

/**
 * Создается таблица "Способы погрузок транспорта перевозчиков".
 */
class m180420_091119_create_transport_load_types_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Способы погрузок транспорта перевозчиков"';
        };

        $this->createTable('transport_load_types', [
            'id' => $this->primaryKey(),
            'transport_id' => $this->integer()->notNull()->comment('Транспортное средство'),
            'lt_id' => $this->integer()->notNull()->comment('Тип погрузки'),
        ], $tableOptions);

        $this->createIndex('transport_id', 'transport_load_types', 'transport_id');
        $this->createIndex('lt_id', 'transport_load_types', 'lt_id');

        $this->addForeignKey('fk_transport_load_types_transport_id', 'transport_load_types', 'transport_id', 'transport', 'id');
        $this->addForeignKey('fk_transport_load_types_lt_id', 'transport_load_types', 'lt_id', 'load_types', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_transport_load_types_lt_id', 'transport_load_types');
        $this->dropForeignKey('fk_transport_load_types_transport_id', 'transport_load_types');

        $this->dropIndex('lt_id', 'transport_load_types');
        $this->dropIndex('transport_id', 'transport_load_types');

        $this->dropTable('transport_load_types');
    }
}
