<?php

use yii\db\Migration;

/**
 * В таблицу организаций добавляется нумератор для исходящей корреспонденции.
 */
class m200317_151122_adding_om_num_tmpl_to_organizations extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'organizations';

    /**
     * Поля
     */
    const FIELD_OM_TPL_NAME = 'om_num_tmpl';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_OM_TPL_NAME, $this->string(30)->comment('Шаблон номера исходящей корреспонденции') . ' AFTER `im_num_tmpl`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_OM_TPL_NAME);
    }
}
