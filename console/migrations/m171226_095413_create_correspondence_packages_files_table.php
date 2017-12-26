<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы пакетов корреспонденции".
 */
class m171226_095413_create_correspondence_packages_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы пакетов корреспонденции"';
        };

        $this->createTable('correspondence_packages_files', [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            'uploaded_by' => $this->integer()->notNull()->comment('Автор загрузки'),
            'cp_id' => $this->integer()->notNull()->comment('Пакет корреспонденции'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('uploaded_by', 'correspondence_packages_files', 'uploaded_by');
        $this->createIndex('cp_id', 'correspondence_packages_files', 'cp_id');

        $this->addForeignKey('fk_correspondence_packages_files_uploaded_by', 'correspondence_packages_files', 'uploaded_by', 'user', 'id');
        $this->addForeignKey('fk_correspondence_packages_files_cp_id', 'correspondence_packages_files', 'cp_id', 'correspondence_packages', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_correspondence_packages_files_cp_id', 'correspondence_packages_files');
        $this->dropForeignKey('fk_correspondence_packages_files_uploaded_by', 'correspondence_packages_files');

        $this->dropIndex('cp_id', 'correspondence_packages_files');
        $this->dropIndex('uploaded_by', 'correspondence_packages_files');

        $this->dropTable('correspondence_packages_files');
    }
}
