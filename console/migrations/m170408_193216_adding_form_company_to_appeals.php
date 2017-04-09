<?php

use yii\db\Migration;

class m170408_193216_adding_form_company_to_appeals extends Migration
{
    public function up()
    {
        $this->addColumn('appeals', 'form_company', $this->string(50)->comment('Поле формы Компания') . ' AFTER `state_id`');
    }

    public function down()
    {
        $this->dropColumn('appeals', 'form_company');
    }
}
