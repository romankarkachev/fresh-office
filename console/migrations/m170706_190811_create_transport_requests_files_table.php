<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы к запросам на транспорт".
 */
class m170706_190811_create_transport_requests_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы к запросам на транспорт"';
        };

        $this->createTable('transport_requests_files', [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            'uploaded_by' => $this->integer()->notNull()->comment('Автор загрузки'),
            'tr_id' => $this->integer()->notNull()->comment('Запрос на транспорт'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('uploaded_by', 'transport_requests_files', 'uploaded_by');
        $this->createIndex('tr_id', 'transport_requests_files', 'tr_id');

        $this->addForeignKey('fk_transport_requests_files_uploaded_by', 'transport_requests_files', 'uploaded_by', 'user', 'id');
        $this->addForeignKey('fk_transport_requests_files_tr_id', 'transport_requests_files', 'tr_id', 'transport_requests', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_transport_requests_files_tr_id', 'transport_requests_files');
        $this->dropForeignKey('fk_transport_requests_files_uploaded_by', 'transport_requests_files');

        $this->dropIndex('tr_id', 'transport_requests_files');
        $this->dropIndex('uploaded_by', 'transport_requests_files');

        $this->dropTable('transport_requests_files');
    }
}
