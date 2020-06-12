<?php

use yii\db\Migration;

/**
 * Вставляется новое значение в таблицу статусов платежных ордеров.
 */
class m191210_112754_insert_into_payment_orders_states extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'payment_orders_states';

    /**
     * Поля
     */
    const NEW_VALUE_ID = 7;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert(self::TABLE_NAME, [
            'id' => self::NEW_VALUE_ID,
            'name' => 'Отклоненный вансовый отчет',
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
