<?php

use yii\db\Migration;

/**
 * Создается таблица "Транспорт перевозчиков".
 */
class m170604_092945_create_transport_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Транспорт перевозчиков"';
        };

        $this->createTable('transport', [
            'id' => $this->primaryKey(),
            'ferryman_id' => $this->integer()->notNull()->comment('Перевозчик'),
            'tt_id' => $this->integer()->comment('Тип'),
            'vin' => $this->string(50)->comment('VIN'),
            'vin_index' => $this->string(50)->comment('VIN (для поиска)'),
            'rn' => $this->string(30)->comment('Госномер'),
            'rn_index' => $this->string(30)->comment('Госномер (для поиска)'),
            'trailer_rn' => $this->string(30)->comment('Прицеп'),
            'tc_id' => $this->integer()->comment('Техническое состояние'),
            'comment' => $this->text()->comment('Примечание'),
        ], $tableOptions);

        $this->createIndex('ferryman_id', 'transport', 'ferryman_id');
        $this->createIndex('vin_index', 'transport', 'vin_index');
        $this->createIndex('rn_index', 'transport', 'rn_index');
        $this->createIndex('tt_id', 'transport', 'tt_id');
        $this->createIndex('tc_id', 'transport', 'tc_id');

        $this->addForeignKey('fk_transport_ferryman_id', 'transport', 'ferryman_id', 'ferrymen', 'id');
        $this->addForeignKey('fk_transport_tt_id', 'transport', 'tt_id', 'transport_types', 'id');
        $this->addForeignKey('fk_transport_tc_id', 'transport', 'tc_id', 'technical_conditions', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_transport_tc_id', 'transport');
        $this->dropForeignKey('fk_transport_tt_id', 'transport');
        $this->dropForeignKey('fk_transport_ferryman_id', 'transport');

        $this->dropIndex('tc_id', 'transport');
        $this->dropIndex('tt_id', 'transport');
        $this->dropIndex('rn_index', 'transport');
        $this->dropIndex('vin_index', 'transport');
        $this->dropIndex('ferryman_id', 'transport');

        $this->dropTable('transport');
    }
}
