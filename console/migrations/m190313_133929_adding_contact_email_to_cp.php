<?php

use yii\db\Migration;

/**
 * Добавляется поле "E-mail для уведомлений о состоянии почтового отправления".
 */
class m190313_133929_adding_contact_email_to_cp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('correspondence_packages', 'contact_email', $this->string()->defaultExpression('NULL')->comment('E-mail для уведомлений о состоянии почтового отправления') . ' AFTER `contact_person`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('correspondence_packages', 'contact_email');
    }
}
