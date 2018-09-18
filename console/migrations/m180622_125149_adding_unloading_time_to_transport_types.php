<?php

use yii\db\Migration;

/**
 * Добавляется поле "Время на разгрузку" в таблицу типов транспорта.
 */
class m180622_125149_adding_unloading_time_to_transport_types extends Migration
{
    public function up()
    {
        $this->addColumn('transport_types', 'unloading_time', $this->integer()->comment('Время на разгрузку (мин.)'));
    }

    public function down()
    {
        $this->dropColumn('transport_types', 'unloading_time');
    }
}
