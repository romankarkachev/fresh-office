<?php

use yii\db\Migration;

/**
 * В таблицу платежных ордеров по перевозчикам добавляется поле "Оплатить до".
 */
class m190904_093358_enhancing_payment_orders extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('payment_orders', 'pay_till', $this->date()->comment('Оплатить до') . ' AFTER `pd_id`');

        $this->addColumn('payment_orders', 'ccp_at', $this->integer()->comment('Дата и время прикрепления акта выполненных работ') . ' AFTER `approved_at`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('payment_orders', 'pay_till');

        $this->dropColumn('payment_orders', 'ccp_at');
    }
}
