<?php

use yii\db\Migration;

/**
 * В таблицу организаций добавляется нумератор для входящей корреспонденции.
 */
class m191211_111837_adding_tmpl_cp_num extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'organizations';

    /**
     * Поля
     */
    const FIELD_TEMPLATE_NAME = 'im_num_tmpl';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_TEMPLATE_NAME, $this->string(30)->comment('Шаблон номера входящей корреспонденции') . ' AFTER `doc_num_tmpl`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_TEMPLATE_NAME);
    }
}
