<?php

use yii\db\Migration;

/**
 * Создается таблица "Проекты и контрагенты, для которых предоставление ТТН обязательно".
 */
class m190607_073230_create_storage_ttn_required_table extends Migration
{
    /**
     * Наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'storage_ttn_required';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Проекты и контрагенты, для которых предоставление ТТН обязательно"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'type' => 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Тип сущности (1 - контрагент, 2 - ответственный, 3 - проект)"',
            'entity_id' => $this->integer()->notNull()->comment('Идентификатор сущности'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
