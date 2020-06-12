<?php

use yii\db\Migration;

/**
 * Создается таблица "Журнал событий проектов по экологии".
 */
class m200525_170527_create_eco_projects_logs_table extends Migration
{
    /**
     * Наименование таблицы, которая создается
     */
    const TABLE_NAME = 'eco_projects_logs';

    /**
     * Поля, которые имеют индексы
     */
    const FIELD_CREATED_BY_NAME = 'created_by';
    const FIELD_PROJECT_ID_NAME = 'project_id';
    const FIELD_STATE_ID_NAME = 'state_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_CREATED_BY_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_CREATED_BY_NAME;
    const FK_PROJECT_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_PROJECT_ID_NAME;
    const FK_STATE_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_STATE_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Журнал событий проектов по экологии"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->comment('Инициатор'),
            self::FIELD_PROJECT_ID_NAME => $this->integer()->notNull()->comment('Проект'),
            self::FIELD_STATE_ID_NAME => $this->integer()->comment('Статус'),
            'description' => $this->text()->comment('Описание события'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_PROJECT_ID_NAME, self::TABLE_NAME, self::FIELD_PROJECT_ID_NAME);
        $this->createIndex(self::FIELD_STATE_ID_NAME, self::TABLE_NAME, self::FIELD_STATE_ID_NAME);

        $this->addForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey(self::FK_PROJECT_ID_NAME, self::TABLE_NAME, self::FIELD_PROJECT_ID_NAME, 'eco_projects', 'id');
        $this->addForeignKey(self::FK_STATE_ID_NAME, self::TABLE_NAME, self::FIELD_STATE_ID_NAME, 'states_eco_projects', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_STATE_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_PROJECT_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_STATE_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_PROJECT_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
