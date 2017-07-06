<?php

use yii\db\Migration;

/**
 * Добавляется поле "Спецтехника", которое придает при взведенном значении типу техники соответствующее свойство.
 */
class m170704_215932_adding_spec_to_transport_types extends Migration
{
    public function up()
    {
        $this->addColumn('transport_types', 'is_spec', 'TINYINT(1) DEFAULT "0" COMMENT "Является спецтехникой"');
    }

    public function down()
    {
        $this->dropColumn('transport_types', 'is_spec');
    }
}
