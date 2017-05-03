<?php

use yii\db\Migration;

/**
 * Добавляется поле "Раздел учета" в таблицу ответственных для новых клиентов.
 */
class m170502_103938_adding_account_section_to_responsible_for_new_ca extends Migration
{
    public function up()
    {
        $this->addColumn('responsible_for_new_ca', 'ac_id', 'TINYINT(1) DEFAULT 1 COMMENT "Раздел учета (1 - утилизация, 2 - экология)"');
    }

    public function down()
    {
        $this->dropColumn('responsible_for_new_ca', 'ac_id');
    }
}
