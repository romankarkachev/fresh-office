<?php

use yii\db\Migration;

/**
 * Создается таблица, которая будет содержать идентификаторы контрагентов, признанных не дубликатами.
 */
class m200610_140455_create_fo_ca_di_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'fo_ca_di';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
