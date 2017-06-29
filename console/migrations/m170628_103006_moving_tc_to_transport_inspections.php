<?php

use yii\db\Migration;

/**
 * Колонка "Техническое состояние" перемещается из таблицы "Транспорт" в таблицу "Техническое состояние транспортных
 * средств". Также добавляется колонка "Комментарий" в эту таблицу.
 */
class m170628_103006_moving_tc_to_transport_inspections extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_transport_tc_id', 'transport');
        $this->dropIndex('tc_id', 'transport');
        $this->dropColumn('transport', 'tc_id');

        $this->addColumn('transport_inspections', 'tc_id', $this->integer()->comment('Техническое состояние'));
        $this->createIndex('tc_id', 'transport_inspections', 'tc_id');
        $this->addForeignKey('fk_transport_inspections_tc_id', 'transport_inspections', 'tc_id', 'technical_conditions', 'id');

        $this->addColumn('transport_inspections', 'comment', $this->text()->comment('Комментарий'));
    }

    public function down()
    {
        $this->dropForeignKey('fk_transport_inspections_tc_id', 'transport_inspections');
        $this->dropIndex('tc_id', 'transport_inspections');
        $this->dropColumn('transport_inspections', 'tc_id');

        $this->addColumn('transport', 'tc_id', $this->integer()->comment('Техническое состояние') . ' AFTER `trailer_rn`');
        $this->createIndex('tc_id', 'transport', 'tc_id');
        $this->addForeignKey('fk_transport_tc_id', 'transport', 'tc_id', 'technical_conditions', 'id');

        $this->dropColumn('transport_inspections', 'comment');
    }
}
