<?php

use yii\db\Migration;

/**
 * В таблицу входящей корреспонденции добавляется поле "Входящая дата".
 */
class m200208_081720_adding_inc_date_to_incoming_mail extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('incoming_mail', 'inc_date', $this->date()->comment('Входящая дата') . ' AFTER `inc_num`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('incoming_mail', 'inc_date');
    }
}
