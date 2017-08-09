<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Наличие смартфона с камерой".
 */
class m170809_202223_adding_has_smartphone_to_drivers extends Migration
{
    public function up()
    {
        $this->addColumn('drivers', 'has_smartphone', 'TINYINT(1) DEFAULT "0" COMMENT "Наличие смартфона с камерой"');
    }

    public function down()
    {
        $this->dropColumn('drivers', 'has_smartphone');
    }
}
