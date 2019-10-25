<?php

use yii\db\Migration;

/**
 * Добавляется поле "Дата и время отправки уведомления контактному лицу о поступлении почтового отправления в отделение".
 */
class m190313_140558_adding_delivery_notified_at_to_cp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('correspondence_packages', 'delivery_notified_at', $this->integer()->comment('Дата и время отправки уведомления контактному лицу о поступлении почтового отправления в отделение'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('correspondence_packages', 'delivery_notified_at');
    }
}
