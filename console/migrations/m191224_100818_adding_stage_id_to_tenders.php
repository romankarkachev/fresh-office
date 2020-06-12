<?php

use yii\db\Migration;

/**
 * В таблицу тендеров добавляется поле "Этап закупки".
 */
class m191224_100818_adding_stage_id_to_tenders extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'tenders';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_STAGE_ID_NAME = 'stage_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_STAGE_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_STAGE_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_STAGE_ID_NAME, $this->integer()->comment('Этап закупки') . ' AFTER `state_id`');

        $this->createIndex(self::FIELD_STAGE_ID_NAME, self::TABLE_NAME, self::FIELD_STAGE_ID_NAME);

        $this->addForeignKey(self::FK_STAGE_ID_NAME, self::TABLE_NAME, self::FIELD_STAGE_ID_NAME, 'tenders_stages', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_STAGE_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_STAGE_ID_NAME, self::TABLE_NAME);

        $this->dropColumn(self::TABLE_NAME, self::FIELD_STAGE_ID_NAME);
    }
}
