<?php

use yii\db\Migration;

/**
 * В профиль пользователя добавляется колонка, где будет храниться идентификатор пользователя по данным Fresh Office.
 */
class m171110_093305_adding_fo_id_to_profile extends Migration
{
    public function up()
    {
        $this->addColumn('profile', 'fo_id', $this->integer()->comment('ID во Fresh Office') . ' AFTER `name`');
    }

    public function down()
    {
        $this->dropColumn('profile', 'fo_id');
    }
}
