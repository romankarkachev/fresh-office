<?php

use yii\db\Migration;

/**
 * Добавляется поле "Лимит отправок через курьерскую службу Major Express".
 */
class m180130_160402_adding_limit_cp_me_to_profile extends Migration
{
    public function up()
    {
        $this->addColumn('profile', 'limit_cp_me', 'TINYINT(1) UNSIGNED COMMENT"Лимит отправок через курьерскую службу Major Express"');
    }

    public function down()
    {
        $this->dropColumn('profile', 'limit_cp_me');
    }
}
