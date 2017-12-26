<?php

use yii\db\Migration;

/**
 * Добавляется поле "Телефон 2" к водителям.
 */
class m171205_090719_adding_phone2_to_drivers extends Migration
{
    public function up()
    {
        $this->addColumn('drivers', 'phone2', $this->string(10)->comment('Телефон 2') . ' AFTER `phone`');
    }

    public function down()
    {
        $this->dropColumn('drivers', 'phone2');
    }
}
