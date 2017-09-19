<?php

use yii\db\Migration;

/**
 * Поле "ID в источнике" не должно быть обязательным.
 */
class m170917_220850_making_src_id_null_in_counteragents_post_addresses extends Migration
{
    public function up()
    {
        $this->alterColumn('counteragents_post_addresses', 'src_id', $this->integer()->comment('ID в источнике'));
    }

    public function down()
    {
        return true;
    }
}
