<?php

use yii\db\Migration;

/**
 * Добавляется поле "Дата выдачи водительского удостоверения".
 */
class m170628_072846_adding_dl_issued_at_to_drivers extends Migration
{
    public function up()
    {
        $this->addColumn('drivers', 'dl_issued_at', $this->date()->comment('Дата выдачи водительского удостоверения') . ' AFTER `driver_license`');
    }

    public function down()
    {
        $this->dropColumn('drivers', 'dl_issued_at');
    }
}
