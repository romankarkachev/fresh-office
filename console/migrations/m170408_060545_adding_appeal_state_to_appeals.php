<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Статус обращения" в таблицу обращений.
 */
class m170408_060545_adding_appeal_state_to_appeals extends Migration
{
    public function up()
    {
        $this->addColumn('appeals', 'state_id', $this->integer()->notNull()->defaultValue(1)->comment('Статус обращения (1 - новое, 2 - выбор ответственного, 3 - ожидает оплаты, 4 - закрыто, 5 - конверсия, 6 - отказ') . ' AFTER `created_at`');
    }

    public function down()
    {
        $this->dropColumn('appeals', 'state_id');
    }
}
