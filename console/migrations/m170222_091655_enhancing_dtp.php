<?php

use yii\db\Migration;

class m170222_091655_enhancing_dtp extends Migration
{
    public function up()
    {
        $this->addColumn('documents_tp', 'dc', $this->string(10)->comment('Класс опасности'));
        $this->addColumn('documents_tp', 'is_printable', 'TINYINT(1) DEFAULT 1 COMMENT "Выводить на печать"');
    }

    public function down()
    {
        $this->dropColumn('documents_tp', 'dc');
        $this->dropColumn('documents_tp', 'is_printable');
    }
}
