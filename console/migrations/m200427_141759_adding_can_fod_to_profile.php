<?php

use yii\db\Migration;

/**
 * Добавляется поле, позволяющее управлять видимостью элемента меню и возможностью передачи денежных средств.
 */
class m200427_141759_adding_can_fod_to_profile extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'profile';

    /**
     * Поля
     */
    const FIELD_CAN_FOD_NAME = 'can_fod';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_CAN_FOD_NAME, 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Возможность делегировать свои финансовые обязательства другому подотчетному лицу"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_CAN_FOD_NAME);
    }
}
