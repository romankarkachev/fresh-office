<?php

use yii\db\Migration;

/**
 * Добавляется поле "Количество дней постоплаты" в таблицу перевозчиков.
 */
class m180910_144946_adding_ppdq_to_ferrymen extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen', 'ppdq', $this->integer()->comment('Количество дней постоплаты'));
    }

    public function down()
    {
        $this->dropColumn('ferrymen', 'ppdq');
    }
}
