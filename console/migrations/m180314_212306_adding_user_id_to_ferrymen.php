<?php

use yii\db\Migration;

/**
 * В таблицу перевозчиков добавляется поле со ссылкой на пользователя системы.
 */
class m180314_212306_adding_user_id_to_ferrymen extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen', 'user_id', $this->integer()->unique()->comment('Пользователь системы'));
        $this->addForeignKey('fk_ferrymen_user_id', 'ferrymen', 'user_id', 'user', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_ferrymen_user_id', 'ferrymen');
        $this->dropIndex('user_id', 'ferrymen');
        $this->dropColumn('ferrymen', 'user_id');
    }
}
