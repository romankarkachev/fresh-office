<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Статус" в таблицы перевозчиков, водителей, транспорта.
 */
class m170724_160208_adding_state_to_ferrymen extends Migration
{
    /**
     * @var string
     */
    const COMMENT = 'Статус (1 - нареканий нет, 2 - есть замечания, 3 - черный список)';

    public function up()
    {
        $this->addColumn('ferrymen', 'state_id', 'TINYINT(1) DEFAULT"1" COMMENT "' . self::COMMENT . '" AFTER `pc_id`');
        $this->addColumn('drivers', 'state_id', 'TINYINT(1) DEFAULT"1" COMMENT "' . self::COMMENT . '" AFTER `ferryman_id`');
        $this->addColumn('transport', 'state_id', 'TINYINT(1) DEFAULT"1" COMMENT "' . self::COMMENT . '" AFTER `ferryman_id`');
    }

    public function down()
    {
        $this->dropColumn('ferrymen', 'state_id');
        $this->dropColumn('drivers', 'state_id');
        $this->dropColumn('transport', 'state_id');
    }
}
