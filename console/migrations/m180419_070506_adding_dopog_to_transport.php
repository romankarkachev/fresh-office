<?php

use yii\db\Migration;

/**
 * Добавляется колонка "ДОПОГ" в таблицу транспорта перевозчиков.
 */
class m180419_070506_adding_dopog_to_transport extends Migration
{
    public function up()
    {
        $this->addColumn('transport', 'is_dopog', 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"ДОПОГ (допуск на перевозку опасных грузов)" AFTER `osago_expires_at`');
    }

    public function down()
    {
        $this->dropColumn('transport', 'is_dopog');
    }
}
