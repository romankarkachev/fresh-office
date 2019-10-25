<?php

use yii\db\Migration;

/**
 * Добавляются несколько полей.
 */
class m191017_094927_enhancing_tenders extends Migration
{
    /**
     * Наименование таблицы, в которую вносятся изменения
     */
    const TABLE_NAME = 'tenders';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_TK_ID_NAME = 'tk_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_TK_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_TK_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'amount_fo', $this->decimal(12, 2)->comment('Сумма обеспечения заявки (order funding)') . ' AFTER `amount_offer`');
        $this->addColumn(self::TABLE_NAME, 'amount_fc', $this->decimal(12, 2)->comment('Сумма обеспечения договора (contract funding)') . ' AFTER `amount_fo`');

        $this->addColumn(self::TABLE_NAME, self::FIELD_TK_ID_NAME, $this->integer()->comment('Вид конкурса') . ' AFTER `created_by`');
        $this->createIndex(self::FIELD_TK_ID_NAME, self::TABLE_NAME, self::FIELD_TK_ID_NAME);
        $this->addForeignKey(self::FK_TK_ID_NAME, self::TABLE_NAME, self::FIELD_TK_ID_NAME, 'tenders_kinds', 'id');

        $this->alterColumn(self::TABLE_NAME, 'fo_ca_id', $this->integer()->comment('Контрагент'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(self::TABLE_NAME, 'fo_ca_id', $this->integer()->notNull()->comment('Контрагент'));

        $this->dropForeignKey(self::FK_TK_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_TK_ID_NAME, self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::FIELD_TK_ID_NAME);

        $this->dropColumn(self::TABLE_NAME, 'amount_fc');
        $this->dropColumn(self::TABLE_NAME, 'amount_fo');
    }
}
