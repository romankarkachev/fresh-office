<?php

use yii\db\Migration;

/**
 * Создается таблица "Динамические поля форм для участия в закупках".
 */
class m200605_092446_create_tfk_fields_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'tfk_fields';

    /**
     * Поля
     */
    const FIELD_KIND_ID_NAME = 'kind_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_KIND_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_KIND_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Динамические поля форм для участия в закупках"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            self::FIELD_KIND_ID_NAME => $this->integer()->notNull()->comment('Форма'),
            'alias' => $this->string()->notNull()->comment('Псевдоним'),
            'name' => $this->string()->notNull()->comment('Наименование'),
            'description' => $this->string()->comment('Описание'),
            'widget' => $this->string(40)->comment('Виджет, применяемый для ввода значения в это поле'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_KIND_ID_NAME, self::TABLE_NAME, self::FIELD_KIND_ID_NAME);

        $this->addForeignKey(self::FK_KIND_ID_NAME, self::TABLE_NAME, self::FIELD_KIND_ID_NAME, 'tf_kinds', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_KIND_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_KIND_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
