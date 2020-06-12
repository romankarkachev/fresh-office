<?php

use yii\db\Migration;

/**
 * Создается таблица "Наборы форм для участия в тендерах".
 */
class m200602_091850_create_tf_vk_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'tf_vk';

    /**
     * Поля
     */
    const FIELD_VARIETY_ID_NAME = 'variety_id';
    const FIELD_KIND_ID_NAME = 'kind_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_VARIETY_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_VARIETY_ID_NAME;
    const FK_KIND_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_KIND_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Наборы форм для участия в тендерах"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            self::FIELD_VARIETY_ID_NAME => $this->integer()->comment('Разновидность'),
            self::FIELD_KIND_ID_NAME => $this->integer()->comment('Форма'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_VARIETY_ID_NAME, self::TABLE_NAME, self::FIELD_VARIETY_ID_NAME);
        $this->createIndex(self::FIELD_KIND_ID_NAME, self::TABLE_NAME, self::FIELD_KIND_ID_NAME);

        $this->addForeignKey(self::FK_VARIETY_ID_NAME, self::TABLE_NAME, self::FIELD_VARIETY_ID_NAME, 'tf_varieties', 'id');
        $this->addForeignKey(self::FK_KIND_ID_NAME, self::TABLE_NAME, self::FIELD_KIND_ID_NAME, 'tf_kinds', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_KIND_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_VARIETY_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_KIND_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_VARIETY_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
