<?php

use yii\db\Migration;

/**
 * В таблицу тендеров вносятся изменения в части обязательности некоторых полей.
 */
class m191009_092437_enhancing_tenders extends Migration
{
    /**
     * Наименование таблицы, в которую вносятся изменения
     */
    const TABLE_NAME = 'tenders';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(self::TABLE_NAME, 'date_complete', $this->date()->comment('Срок выполнения работ (услуг)'));
        $this->alterColumn(self::TABLE_NAME, 'deferral', 'TINYINT(1) DEFAULT"0" COMMENT"Срок оплаты (количество дней отсрочки платежа)"');

        $this->addColumn(self::TABLE_NAME, 'law_no', $this->string(20)->comment('Номер закона, по которому осуществляется закупка') . ' AFTER `created_by`');
        $this->addColumn(self::TABLE_NAME, 'date_auction', $this->integer()->comment('Дата проведения аукциона') . ' AFTER `date_sumup`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'date_auction');
        $this->dropColumn(self::TABLE_NAME, 'law_no');

        $this->alterColumn(self::TABLE_NAME, 'deferral', 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Срок оплаты (количество дней отсрочки платежа)"');
        $this->alterColumn(self::TABLE_NAME, 'date_complete', $this->date()->notNull()->comment('Срок выполнения работ (услуг)'));
    }
}
