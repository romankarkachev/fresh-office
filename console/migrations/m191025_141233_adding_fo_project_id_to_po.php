<?php

use yii\db\Migration;

/**
 * Добавляется поле "ID проекта в платежные ордеры по бюджету".
 */
class m191025_141233_adding_fo_project_id_to_po extends Migration
{
    /**
     * Наименование таблицы, в которую вносятся изменения
     */
    const TABLE_NAME = 'po';

    /**
     * Наименование поля, которое добавляется
     */
    const FIELD_NAME = 'fo_project_id';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_NAME, $this->integer()->comment('ID проекта с CRM Fresh Office') . ' AFTER `paid_at`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_NAME);
    }
}
