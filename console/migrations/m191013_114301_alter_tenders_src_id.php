<?php

use yii\db\Migration;

/**
 * Тип значения поля "Идентификатор файла в источнике" меняется на строковый ввиду того, что по 44-му закону идентификаторы
 * файлов записаны буквами и цифрами.
 */
class m191013_114301_alter_tenders_src_id extends Migration
{
    /**
     * Наименование таблицы, в которую вносятся изменения
     */
    const TABLE_NAME = 'tenders_files';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(self::TABLE_NAME, 'src_id', $this->string(50)->comment('Идентификатор в источнике'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(self::TABLE_NAME, 'src_id', $this->integer()->comment('Идентификатор в источнике'));
    }
}
