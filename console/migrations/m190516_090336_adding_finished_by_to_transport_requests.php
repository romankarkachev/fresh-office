<?php

use yii\db\Migration;

/**
 * Добавляется поле "Кем закрыт" в запросы на транспорт.
 */
class m190516_090336_adding_finished_by_to_transport_requests extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'transport_requests';

    /**
     * Поля, которые имеют индексы
     */
    const FIELD_FINISHED_BY = 'finished_by';

    /**
     * @var string наименование внешнего ключа для добавляемого поля finished_by
     */
    const FK_FINISHED_BY_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_FINISHED_BY;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_FINISHED_BY, $this->integer()->comment('Кем закрыт') . ' AFTER `finished_at`');

        $this->createIndex(self::FIELD_FINISHED_BY, self::TABLE_NAME, self::FIELD_FINISHED_BY);

        $this->addForeignKey(self::FK_FINISHED_BY_NAME, self::TABLE_NAME, self::FIELD_FINISHED_BY, 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_FINISHED_BY_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_FINISHED_BY, self::TABLE_NAME);

        $this->dropColumn(self::TABLE_NAME, self::FIELD_FINISHED_BY);
    }
}
