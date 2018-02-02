<?php

use yii\db\Migration;

/**
 * Создается таблица "Статистика по файловому хранилищу".
 */
class m180131_171043_create_file_storage_stats_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Статистика по файловому хранилищу"';
        };

        $this->createTable('file_storage_stats', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'created_by' => $this->integer()->comment('Автор создания'),
            'type' => 'TINYINT(1) COMMENT"Тип показателя (1 - просмотр, 2 - скачивание)"',
            'fs_id' => $this->integer()->notNull()->comment('Файл'),
        ], $tableOptions);

        $this->createIndex('created_by', 'file_storage_stats', 'created_by');
        $this->createIndex('fs_id', 'file_storage_stats', 'fs_id');

        $this->addForeignKey('fk_file_storage_stats_created_by', 'file_storage_stats', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_file_storage_stats_fs_id', 'file_storage_stats', 'fs_id', 'file_storage', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_file_storage_stats_fs_id', 'file_storage_stats');
        $this->dropForeignKey('fk_file_storage_stats_created_by', 'file_storage_stats');

        $this->dropIndex('fs_id', 'file_storage_stats');
        $this->dropIndex('created_by', 'file_storage_stats');

        $this->dropTable('file_storage_stats');
    }
}
