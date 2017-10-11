<?php

use yii\db\Migration;

/**
 * Создается таблица "Коды ФККО запросов лицензий".
 */
class m171011_175412_create_licenses_requests_fkko_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Коды ФККО запросов лицензий"';
        };

        $this->createTable('licenses_requests_fkko', [
            'id' => $this->primaryKey(),
            'fkko_id' => $this->integer()->notNull()->comment('ФККО'),
            'file_id' => $this->integer()->comment('Файл со сканом'),
        ], $tableOptions);

        $this->createIndex('fkko_id', 'licenses_requests_fkko', 'fkko_id');
        $this->createIndex('file_id', 'licenses_requests_fkko', 'file_id');

        $this->addForeignKey('fk_licenses_requests_fkko_fkko_id', 'licenses_requests_fkko', 'fkko_id', 'fkko', 'id');
        $this->addForeignKey('fk_licenses_requests_fkko_file_id', 'licenses_requests_fkko', 'file_id', 'licenses_files', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_licenses_requests_fkko_file_id', 'licenses_requests_fkko');
        $this->dropForeignKey('fk_licenses_requests_fkko_fkko_id', 'licenses_requests_fkko');

        $this->dropIndex('file_id', 'licenses_requests_fkko');
        $this->dropIndex('fkko_id', 'licenses_requests_fkko');

        $this->dropTable('licenses_requests_fkko');
    }
}
