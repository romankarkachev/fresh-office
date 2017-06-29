<?php

use yii\db\Migration;

/**
 * Добавляются поля с паспортными данными в таблицу водителей.
 */
class m170626_173639_adding_passport_data_to_drivers extends Migration
{
    public function up()
    {
        $this->addColumn('drivers', 'pass_serie', $this->string(10)->comment('Паспорт серия'));
        $this->addColumn('drivers', 'pass_num', $this->string(10)->comment('Паспорт номер'));
        $this->addColumn('drivers', 'pass_issued_at', $this->date()->comment('Паспорт дата выдачи'));
        $this->addColumn('drivers', 'pass_issued_by', $this->string(150)->comment('Паспорт кем выдан'));
    }

    public function down()
    {
        $this->dropColumn('drivers', 'pass_serie');
        $this->dropColumn('drivers', 'pass_num');
        $this->dropColumn('drivers', 'pass_issued_at');
        $this->dropColumn('drivers', 'pass_issued_by');
    }
}
