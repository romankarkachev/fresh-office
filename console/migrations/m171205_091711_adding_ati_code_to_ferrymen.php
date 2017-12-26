<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Код АТИ" в таблицу перевозчиков.
 */
class m171205_091711_adding_ati_code_to_ferrymen extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen', 'ati_code', $this->string(10)->comment('Код АТИ'));
    }

    public function down()
    {
        $this->dropColumn('ferrymen', 'ati_code');
    }
}
