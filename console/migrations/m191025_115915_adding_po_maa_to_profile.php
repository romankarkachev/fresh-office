<?php

use yii\db\Migration;

/**
 * Добавляется поле "Максимальная сумма платежного ордера, которая может быть согласована пользователем без руководства".
 */
class m191025_115915_adding_po_maa_to_profile extends Migration
{
    /**
     * Наименование таблицы, в которую вносятся изменения
     */
    const TABLE_NAME = 'profile';

    /**
     * Наименование поля, которое добавляется
     */
    const FIELD_NAME = 'po_maa';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_NAME, $this->decimal(12,2)->comment('Максимальная сумма платежного ордера, которая может быть согласована пользователем без руководства'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_NAME);
    }
}
