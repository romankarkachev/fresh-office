<?php

use yii\db\Migration;

class m170406_201016_modifying_contract_field_in_documents extends Migration
{
    public function up()
    {
        $this->alterColumn('documents', 'fo_contract', $this->string(100)->comment('Договор из Fresh Office'));
    }

    public function down()
    {
        $this->alterColumn('documents', 'fo_contract', $this->integer()->unsigned()->comment('ID договора во Fresh Office'));
    }
}
