<?php

use yii\db\Migration;

/**
 * Добавляется поле "Изменения в договоре" в таблицу тендеров.
 */
class m200525_170205_adding_state_id_to_eco_projects extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'eco_projects';

    /**
     * Поля
     */
    const FIELD_STATE_ID_NAME = 'state_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_STATE_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_STATE_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_STATE_ID_NAME, $this->integer()->comment('Статус') . ' AFTER `created_by`');
        $this->createIndex(self::FIELD_STATE_ID_NAME, self::TABLE_NAME, self::FIELD_STATE_ID_NAME);
        $this->addForeignKey(self::FK_STATE_ID_NAME, self::TABLE_NAME, self::FIELD_STATE_ID_NAME, 'states_eco_projects', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_STATE_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_STATE_ID_NAME, self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::FIELD_STATE_ID_NAME);
    }
}
