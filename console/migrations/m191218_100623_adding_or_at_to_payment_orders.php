<?php

use yii\db\Migration;

/**
 * Добавляется поле "Дата и время получения оригиналов документов".
 */
class m191218_100623_adding_or_at_to_payment_orders extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'payment_orders';

    /**
     * Поля
     */
    const FIELD_OR_AT_NAME = 'or_at';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_OR_AT_NAME, $this->integer()->comment('Дата и время получения оригиналов документов') . ' AFTER `ccp_at`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_OR_AT_NAME);
    }
}
