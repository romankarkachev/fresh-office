<?php

use yii\db\Migration;

/**
 * В таблицу платежных ордеров по бюджету добавляется поле "ID контрагента из CRM Fresh Office".
 */
class m200403_160344_adding_fo_ca_id_to_po extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'po';

    /**
     * Поля
     */
    const FIELD_CA_ID_NAME = 'fo_ca_id';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_CA_ID_NAME, $this->integer()->comment('ID контрагента из CRM Fresh Office') . ' AFTER `fo_project_id`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_CA_ID_NAME);
    }
}
