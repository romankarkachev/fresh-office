<?php

use yii\db\Migration;

/**
 * Создается таблица "Расположение кодов ФККО на страницах лицензий".
 */
class m171011_063048_create_licenses_fkko_pages_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Расположение кодов ФККО на страницах лицензий"';
        };

        $this->createTable('licenses_fkko_pages', [
            'id' => $this->primaryKey(),
            'fkko_id' => $this->integer()->notNull()->comment('Код ФККО'),
            'file_id' => $this->integer()->notNull()->comment('Файл со сканом страницы'),
        ], $tableOptions);

        $this->createIndex('fkko_id', 'licenses_fkko_pages', 'fkko_id');
        $this->createIndex('file_id', 'licenses_fkko_pages', 'file_id');

        $this->addForeignKey('fk_licenses_fkko_pages_fkko_id', 'licenses_fkko_pages', 'fkko_id', 'fkko', 'id');
        $this->addForeignKey('fk_licenses_fkko_pages_file_id', 'licenses_fkko_pages', 'file_id', 'licenses_files', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_licenses_fkko_pages_file_id', 'licenses_fkko_pages');
        $this->dropForeignKey('fk_licenses_fkko_pages_fkko_id', 'licenses_fkko_pages');

        $this->dropIndex('file_id', 'licenses_fkko_pages');
        $this->dropIndex('fkko_id', 'licenses_fkko_pages');

        $this->dropTable('licenses_fkko_pages');
    }
}
