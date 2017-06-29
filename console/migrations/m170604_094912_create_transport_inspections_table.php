<?php

use yii\db\Migration;

/**
 * Создается таблица "Осмотры транспорта логистами".
 */
class m170604_094912_create_transport_inspections_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Осмотры транспорта логистами"';
        };

        $this->createTable('transport_inspections', [
            'id' => $this->primaryKey(),
            'transport_id' => $this->integer()->notNull()->comment('Транспорт'),
            'inspected_at' => $this->date()->notNull()->comment('Дата осмотра'),
            'place' => $this->string(50)->comment('Место проведения'),
            'responsible' => $this->string(50)->comment('Ответственный'),
        ], $tableOptions);

        $this->createIndex('transport_id', 'transport_inspections', 'transport_id');

        $this->addForeignKey('fk_transport_inspections_transport_id', 'transport_inspections', 'transport_id', 'transport', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_transport_inspections_transport_id', 'transport_inspections');

        $this->dropIndex('transport_id', 'transport_inspections');

        $this->dropTable('transport_inspections');
    }
}
