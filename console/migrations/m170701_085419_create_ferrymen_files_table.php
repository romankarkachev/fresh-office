<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы к перевозчикам".
 */
class m170701_085419_create_ferrymen_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы к перевозчикам"';
        };

        $this->createTable('ferrymen_files', [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            'uploaded_by' => $this->integer()->notNull()->comment('Автор загрузки'),
            'ferryman_id' => $this->integer()->notNull()->comment('Перевозчик'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('uploaded_by', 'ferrymen_files', 'uploaded_by');
        $this->createIndex('ferryman_id', 'ferrymen_files', 'ferryman_id');

        $this->addForeignKey('fk_ferrymen_files_uploaded_by', 'ferrymen_files', 'uploaded_by', 'user', 'id');
        $this->addForeignKey('fk_ferrymen_files_ferryman_id', 'ferrymen_files', 'ferryman_id', 'ferrymen', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_ferrymen_files_ferryman_id', 'ferrymen_files');
        $this->dropForeignKey('fk_ferrymen_files_uploaded_by', 'ferrymen_files');

        $this->dropIndex('ferryman_id', 'ferrymen_files');
        $this->dropIndex('uploaded_by', 'ferrymen_files');

        $this->dropTable('ferrymen_files');
    }
}
