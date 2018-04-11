<?php

use yii\db\Migration;

/**
 * Добавляются признаки удаления записи в таблицы водителей и транспорта перевозчиков.
 * Физически удалить запись может только пользователь с полными правами.
 */
class m180328_202730_adding_is_deleted_to_ferrymen_drivers_and_transport extends Migration
{
    public function up()
    {
        $this->addColumn('drivers', 'is_deleted', 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Признак удаления записи (0 - пометка не установлена, 1 - запись помечена на удаление)" AFTER `ferryman_id`');
        $this->addColumn('transport', 'is_deleted', 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Признак удаления записи (0 - пометка не установлена, 1 - запись помечена на удаление)" AFTER `ferryman_id`');
    }

    public function down()
    {
        $this->dropColumn('drivers', 'is_deleted');
        $this->dropColumn('transport', 'is_deleted');
    }
}
