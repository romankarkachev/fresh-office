<?php

use yii\db\Migration;

/**
 * Добавляется поле "Срок действия полиса ОСАГО" в таблицу транспортных средств перевозчиков.
 */
class m180410_171431_adding_osago_expires_at_to_transport extends Migration
{
    public function up()
    {
        $this->addColumn('transport', 'osago_expires_at', $this->date()->comment('Срок действия полиса ОСАГО') . ' AFTER `trailer_rn`');
    }

    public function down()
    {
        $this->dropColumn('transport', 'osago_expires_at');
    }
}
