<?php

use yii\db\Migration;

/**
 * Добавляется поле "Сложность" в таблицу тендеров.
 */
class m200330_124144_adding_complexity_to_tenders extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'tenders';

    /**
     * Поля
     */
    const FIELD_COMPLEXITY_NAME = 'complexity';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_COMPLEXITY_NAME, 'TINYINT(1) COMMENT"Сложность (диапазон 1-3)" AFTER `is_contract_approved`');

        $this->update(self::TABLE_NAME, [
            self::FIELD_COMPLEXITY_NAME => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_COMPLEXITY_NAME);
    }
}
