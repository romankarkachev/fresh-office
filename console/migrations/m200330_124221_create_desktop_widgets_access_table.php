<?php

use yii\db\Migration;

/**
 * Создается таблица "Использование виджетов для рабочего стола пользователями".
 */
class m200330_124221_create_desktop_widgets_access_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'desktop_widgets_access';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_WIDGET_ID_NAME = 'widget_id';
    const FIELD_ENTITY_ID_NAME = 'entity_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_WIDGET_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_WIDGET_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Использование виджетов для рабочего стола пользователями"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            self::FIELD_WIDGET_ID_NAME => $this->integer()->notNull()->comment('Виджет'),
            'type' => 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Тип (1 - роль, 2 - пользователь)"',
            self::FIELD_ENTITY_ID_NAME => $this->string(64)->notNull()->comment('Идентификатор сущности'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_WIDGET_ID_NAME, self::TABLE_NAME, self::FIELD_WIDGET_ID_NAME);
        $this->createIndex(self::FIELD_ENTITY_ID_NAME, self::TABLE_NAME, self::FIELD_ENTITY_ID_NAME);

        $this->addForeignKey(self::FK_WIDGET_ID_NAME, self::TABLE_NAME, self::FIELD_WIDGET_ID_NAME, 'desktop_widgets', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_WIDGET_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_WIDGET_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_ENTITY_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
