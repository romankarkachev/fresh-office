<?php

use yii\db\Migration;

/**
 * В таблицу с журналом событий по тендерам добавляется поле, разграничивающее внутренние записи и внешние.
 */
class m191010_111420_enhancing_tenders_logs extends Migration
{
    /**
     * Наименование таблицы, в которую вносятся изменения
     */
    const TABLE_NAME = 'tenders_logs';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'type', $this->integer()->comment('Тип журнала (1 - внутренняя запись, 2 - запись с сайта закупок)') . ' AFTER `tender_id`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'type');
    }
}
