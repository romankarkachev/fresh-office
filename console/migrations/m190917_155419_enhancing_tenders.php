<?php

use yii\db\Migration;

/**
 * В тендеры добавляется поля "Дата подведения итогов", а поле "Дата окончания приема заявок" меняет формат.
 */
class m190917_155419_enhancing_tenders extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'tenders';

    /**
     * Поля, которые имеют внешние ключи
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
        $this->addColumn(self::TABLE_NAME, 'placed_at', $this->integer()->comment('Дата размещения извещения') . ' AFTER `conditions`');
        $this->alterColumn(self::TABLE_NAME, 'date_stop', $this->integer()->comment('Дата окончания приема заявок'));
        $this->addColumn(self::TABLE_NAME, 'date_sumup', $this->integer()->comment('Дата подведения итогов') . ' AFTER `date_stop`');
        $this->addColumn(self::TABLE_NAME, 'oos_number', $this->string(25)->comment('Номер закупки') . ' AFTER `created_by`');
        $this->addColumn(self::TABLE_NAME, 'revision', $this->string(3)->comment('Редакция заявки') . ' AFTER `oos_number`');

        $this->addColumn(self::TABLE_NAME, self::FIELD_STATE_ID_NAME, $this->integer()->comment('Статус внутренний') . ' AFTER `revision`');
        $this->createIndex(self::FIELD_STATE_ID_NAME, self::TABLE_NAME, self::FIELD_STATE_ID_NAME);
        $this->addForeignKey(self::FK_STATE_ID_NAME, self::TABLE_NAME, self::FIELD_STATE_ID_NAME, 'tenders_states', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_STATE_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_STATE_ID_NAME, self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::FIELD_STATE_ID_NAME);

        $this->dropColumn(self::TABLE_NAME, 'revision');
        $this->dropColumn(self::TABLE_NAME, 'oos_number');
        $this->dropColumn(self::TABLE_NAME, 'date_sumup');
        $this->alterColumn(self::TABLE_NAME, 'date_stop', $this->date()->comment('Дата окончания приема заявок'));
        $this->dropColumn(self::TABLE_NAME, 'placed_at');
    }
}
