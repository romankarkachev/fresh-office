<?php

use yii\db\Migration;

/**
 * В таблицу платежных ордеров добавляются колонки "Трек номер" и "Статус отправления".
 */
class m200428_141128_enhancing_payment_orders extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'payment_orders';

    /**
     * Поля
     */
    const FIELD_TRACK_NUM_NAME = 'imt_num';
    const FIELD_TRACK_STATE_NAME = 'imt_state';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_TRACK_NUM_NAME, $this->string(50)->comment('Трек-номер') . ' AFTER `or_at`');
        $this->addColumn(self::TABLE_NAME, self::FIELD_TRACK_STATE_NAME, 'TINYINT(1) COMMENT"Статус почтового отправления (1 - в пути, 2 - доставлено, 3 - получено)" AFTER `' . self::FIELD_TRACK_NUM_NAME . '`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_TRACK_STATE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::FIELD_TRACK_NUM_NAME);
    }
}
