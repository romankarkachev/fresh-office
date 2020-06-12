<?php

use yii\db\Migration;

/**
 * Таблица входящей корреспонденции становится таблицей корреспонденции обоих направлений.
 */
class m200317_151100_enhancing_incoming_mail extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'incoming_mail';

    /**
     * Поля
     */
    const FIELD_DIRECTION_NAME = 'direction';
    const FIELD_STATE_ID_NAME = 'state_id';
    const FIELD_TRACK_NUM_NAME = 'track_num';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_STATE_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_STATE_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_DIRECTION_NAME, 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Направление (1 - входящее, 2 - исходящее)" AFTER `created_by`');
        $this->addColumn(self::TABLE_NAME, self::FIELD_STATE_ID_NAME, $this->integer()->comment('Состояние') . ' AFTER `' . self::FIELD_DIRECTION_NAME . '`');
        $this->addColumn(self::TABLE_NAME, self::FIELD_TRACK_NUM_NAME, $this->string(50)->comment('Трек-номер') . ' AFTER `' . self::FIELD_STATE_ID_NAME . '`');

        $this->createIndex(self::FIELD_STATE_ID_NAME, self::TABLE_NAME, self::FIELD_STATE_ID_NAME);
        $this->addForeignKey(self::FK_STATE_ID_NAME, self::TABLE_NAME, self::FIELD_STATE_ID_NAME, 'projects_states', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_STATE_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_STATE_ID_NAME, self::TABLE_NAME);

        $this->dropColumn(self::TABLE_NAME, self::FIELD_TRACK_NUM_NAME);
        $this->dropColumn(self::TABLE_NAME, self::FIELD_STATE_ID_NAME);
        $this->dropColumn(self::TABLE_NAME, self::FIELD_DIRECTION_NAME);
    }
}
