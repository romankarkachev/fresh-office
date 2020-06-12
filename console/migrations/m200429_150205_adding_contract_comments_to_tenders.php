<?php

use yii\db\Migration;

/**
 * Добавляется поле "Изменения в договоре" в таблицу тендеров.
 */
class m200429_150205_adding_contract_comments_to_tenders extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'tenders';

    /**
     * Поля
     */
    const FIELD_CONTRACT_COMMENTS_NAME = 'contract_comments';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_CONTRACT_COMMENTS_NAME, $this->text()->comment('Изменения в договоре') . ' AFTER `lr_id`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_CONTRACT_COMMENTS_NAME);
    }
}
