<?php

use yii\db\Migration;

/**
 * Вставляется новое значение в таблицу статусов платежных ордеров.
 */
class m191204_134820_insert_into_payment_orders_states extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'payment_orders_states';

    /**
     * Поля
     */
    const NEW_VALUE_ID = 6;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert(self::TABLE_NAME, [
            'id' => self::NEW_VALUE_ID,
            'name' => 'Авансовый отчет',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete(self::TABLE_NAME, [
            'id' => self::NEW_VALUE_ID,
        ]);
    }
}
