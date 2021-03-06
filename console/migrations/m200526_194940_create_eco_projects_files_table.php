<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы проектов по экологии".
 */
class m200526_194940_create_eco_projects_files_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'eco_projects_files';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_UPLOADED_BY_NAME = 'uploaded_by';
    const FIELD_EP_ID_NAME = 'project_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_UPLOADED_BY_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_UPLOADED_BY_NAME;
    const FK_EP_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_EP_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы проектов по экологии"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            self::FIELD_UPLOADED_BY_NAME => $this->integer()->notNull()->comment('Автор загрузки'),
            self::FIELD_EP_ID_NAME => $this->integer()->notNull()->comment('Проект'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_UPLOADED_BY_NAME, self::TABLE_NAME, self::FIELD_UPLOADED_BY_NAME);
        $this->createIndex(self::FIELD_EP_ID_NAME, self::TABLE_NAME, self::FIELD_EP_ID_NAME);

        $this->addForeignKey(self::FK_UPLOADED_BY_NAME, self::TABLE_NAME, self::FIELD_UPLOADED_BY_NAME, 'user', 'id');
        $this->addForeignKey(self::FK_EP_ID_NAME, self::TABLE_NAME, self::FIELD_EP_ID_NAME, 'eco_projects', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_EP_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_UPLOADED_BY_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_EP_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_UPLOADED_BY_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
