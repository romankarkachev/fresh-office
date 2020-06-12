<?php

use yii\db\Migration;

/**
 * Добавляется поле "Пометка удаления" в таблицу платежных ордеров по бюджету.
 */
class m191214_154218_adding_is_deleted_to_po extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'po';

    /**
     * Поля
     */
    const FIELD_IS_DELETED_NAME = 'is_deleted';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_IS_DELETED_NAME, 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Пометка удаления"' . ' AFTER `created_by`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_IS_DELETED_NAME);
    }
}
