<?php

use yii\db\Migration;

class m170222_091648_enhancing_documents extends Migration
{
    public function up()
    {
        $this->addColumn('documents', 'doc_num', $this->string(20)->comment('Номер документа').' AFTER `author_id`');
    }

    public function down()
    {
        $this->dropColumn('documents', 'doc_num');
    }
}
