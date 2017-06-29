<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы к транспорту перевозчиков".
 */
class m170629_183557_create_transport_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы к сделкам"';
        };

        $this->createTable('transport_files', [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            'uploaded_by' => $this->integer()->notNull()->comment('Автор загрузки'),
            'transport_id' => $this->integer()->notNull()->comment('Автомобиль'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('uploaded_by', 'transport_files', 'uploaded_by');
        $this->createIndex('transport_id', 'transport_files', 'transport_id');

        $this->addForeignKey('fk_transport_files_uploaded_by', 'transport_files', 'uploaded_by', 'user', 'id');
        $this->addForeignKey('fk_transport_files_transport_id', 'transport_files', 'transport_id', 'transport', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_transport_files_transport_id', 'transport_files');
        $this->dropForeignKey('fk_transport_files_uploaded_by', 'transport_files');

        $this->dropIndex('transport_id', 'transport_files');
        $this->dropIndex('uploaded_by', 'transport_files');

        $this->dropTable('transport_files');
    }
}
