<?php

use yii\db\Migration;

/**
 * Создается таблица "Используемое оборудование для выполнения работ по тендерам".
 */
class m191009_095952_create_tenders_we_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'tenders_we';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_TENDER_ID_NAME = 'tender_id';
    const FIELD_WE_ID_NAME = 'we_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_TENDER_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_TENDER_ID_NAME;
    const FK_WE_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_WE_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Используемое оборудование для выполнения работ по тендерам"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            self::FIELD_TENDER_ID_NAME => $this->integer()->notNull()->comment('Тендер'),
            self::FIELD_WE_ID_NAME => $this->integer()->notNull()->comment('Оборудование'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_TENDER_ID_NAME, self::TABLE_NAME, self::FIELD_TENDER_ID_NAME);
        $this->createIndex(self::FIELD_WE_ID_NAME, self::TABLE_NAME, self::FIELD_WE_ID_NAME);

        $this->addForeignKey(self::FK_TENDER_ID_NAME, self::TABLE_NAME, self::FIELD_TENDER_ID_NAME, 'tenders', 'id');
        $this->addForeignKey(self::FK_WE_ID_NAME, self::TABLE_NAME, self::FIELD_WE_ID_NAME, 'waste_equipment', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_WE_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_TENDER_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_WE_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_TENDER_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
