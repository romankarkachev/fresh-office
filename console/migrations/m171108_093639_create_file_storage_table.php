<?php

use yii\db\Migration;

/**
 * Создается таблица "Файловое хранилище".
 */
class m171108_093639_create_file_storage_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файловое хранилище"';
        };

        $this->createTable('file_storage', [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            'uploaded_by' => $this->integer()->notNull()->comment('Автор загрузки'),
            'ca_id' => $this->integer()->comment('Контрагент'),
            'ca_name' => $this->string()->comment('Контрагент'),
            'type_id' => $this->integer()->comment('Тип контента'),
            'ffp' => $this->string()->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string()->notNull()->comment('Имя файла'),
            'ofn' => $this->string()->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('uploaded_by', 'file_storage', 'uploaded_by');
        $this->createIndex('type_id', 'file_storage', 'type_id');

        $this->addForeignKey('fk_file_storage_uploaded_by', 'file_storage', 'uploaded_by', 'user', 'id');
        $this->addForeignKey('fk_file_storage_type_id', 'file_storage', 'type_id', 'uploading_files_meanings', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_file_storage_type_id', 'file_storage');
        $this->dropForeignKey('fk_file_storage_uploaded_by', 'file_storage');

        $this->dropIndex('type_id', 'file_storage');
        $this->dropIndex('uploaded_by', 'file_storage');

        $this->dropTable('file_storage');
    }
}
