<?php

use yii\db\Migration;

/**
 * В документооборот добавляются поля "Количество дней отсрочки" и "Тип дней".
 */
class m191128_100925_adding_deferral_to_edf extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'edf';

    /**
     * Поля
     */
    const FIELD_DEFERRAL_TYPE_NAME = 'deferral_type';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_DEFERRAL_TYPE_NAME, 'TINYINT(1) DEFAULT"1" COMMENT"Тип дней отсрочки (1 - банковские, 2 - календарные)"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_DEFERRAL_TYPE_NAME);
    }
}
