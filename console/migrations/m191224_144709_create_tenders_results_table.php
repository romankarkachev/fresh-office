<?php

use yii\db\Migration;

/**
 * Создается таблица "Победители в закупках".
 */
class m191224_144709_create_tenders_results_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'tenders_results';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_TENDER_ID_NAME = 'tender_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_TENDER_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_TENDER_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Победители в закупках"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            self::FIELD_TENDER_ID_NAME => $this->integer()->notNull()->comment('Тендер'),
            'placed_at' => $this->integer()->comment('Дата и время размещения реестровой записи'),
            'name' => $this->string()->notNull()->comment('Наименование победителя'),
            'price' => $this->decimal(12,2)->comment('Цена победителя'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_TENDER_ID_NAME, self::TABLE_NAME, self::FIELD_TENDER_ID_NAME);

        $this->addForeignKey(self::FK_TENDER_ID_NAME, self::TABLE_NAME, self::FIELD_TENDER_ID_NAME, 'tenders', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_TENDER_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_TENDER_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
