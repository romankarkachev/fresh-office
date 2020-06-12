<?php

use yii\db\Migration;

/**
 * В таблицу победителей в торгах добавляются поля, дополнительно описывающие таковых.
 */
class m200521_071547_enhancing_tenders_results extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'tenders_results';

    /**
     * Поля
     */
    const FIELD_INN_NAME = 'inn';
    const FIELD_KPP_NAME = 'kpp';
    const FIELD_OGRN_NAME = 'ogrn';
    const FIELD_CA_ID_NAME = 'fo_ca_id';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_INN_NAME, $this->string(12)->comment('ИНН') . ' AFTER `name`');
        $this->addColumn(self::TABLE_NAME, self::FIELD_KPP_NAME, $this->string(9)->comment('КПП') . ' AFTER `' . self::FIELD_INN_NAME . '`');
        $this->addColumn(self::TABLE_NAME, self::FIELD_OGRN_NAME, $this->string(15)->comment('ОГРН(ИП)') . ' AFTER `' . self::FIELD_KPP_NAME . '`');
        $this->addColumn(self::TABLE_NAME, self::FIELD_CA_ID_NAME, $this->integer()->comment('ID контрагента из CRM Fresh Office') . ' AFTER `' . self::FIELD_OGRN_NAME . '`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_CA_ID_NAME);
        $this->dropColumn(self::TABLE_NAME, self::FIELD_OGRN_NAME);
        $this->dropColumn(self::TABLE_NAME, self::FIELD_KPP_NAME);
        $this->dropColumn(self::TABLE_NAME, self::FIELD_INN_NAME);
    }
}
