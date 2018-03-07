<?php

use yii\db\Migration;

/**
 * Добавляется поле "Количество отказов" в пакеты корреспонденции.
 */
class m180302_115200_adding_rejects_count_to_correspondence_packages extends Migration
{
    public function up()
    {
        $this->addColumn('correspondence_packages', 'rejects_count', $this->integer()->notNull()->defaultValue(0)->comment('Количество отказов по пакету'));
    }

    public function down()
    {
        $this->dropColumn('correspondence_packages', 'rejects_count');
    }
}
