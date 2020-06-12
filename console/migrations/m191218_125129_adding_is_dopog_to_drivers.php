<?php

use yii\db\Migration;

/**
 * Добавляется колонка "ДОПОГ" в таблицу водителей перевозчиков.
 */
class m191218_125129_adding_is_dopog_to_drivers extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'drivers';

    /**
     * Поля
     */
    const FIELD_IS_DOPOG_NAME = 'is_dopog';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_IS_DOPOG_NAME, 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"ДОПОГ (допуск на перевозку опасных грузов)"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_IS_DOPOG_NAME);
    }
}
