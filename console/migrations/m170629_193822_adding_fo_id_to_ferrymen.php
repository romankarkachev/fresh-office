<?php

use yii\db\Migration;

/**
 * Добавляется поле "Идентификатор в Fresh Office".
 */
class m170629_193822_adding_fo_id_to_ferrymen extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen', 'fo_id', $this->integer()->comment('Идентификатор в Fresh Office') . ' AFTER `id`');
    }

    public function down()
    {
        $this->dropColumn('ferrymen', 'fo_id');
    }
}
