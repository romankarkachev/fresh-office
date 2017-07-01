<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы к водителям перевозчиков".
 */
class m170701_085412_create_drivers_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы к водителям перевозчиков"';
        };

        $this->createTable('drivers_files', [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            'uploaded_by' => $this->integer()->notNull()->comment('Автор загрузки'),
            'driver_id' => $this->integer()->notNull()->comment('Водитель'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('uploaded_by', 'drivers_files', 'uploaded_by');
        $this->createIndex('driver_id', 'drivers_files', 'driver_id');

        $this->addForeignKey('fk_drivers_files_uploaded_by', 'drivers_files', 'uploaded_by', 'user', 'id');
        $this->addForeignKey('fk_drivers_files_driver_id', 'drivers_files', 'driver_id', 'drivers', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_drivers_files_driver_id', 'drivers_files');
        $this->dropForeignKey('fk_drivers_files_uploaded_by', 'drivers_files');

        $this->dropIndex('driver_id', 'drivers_files');
        $this->dropIndex('uploaded_by', 'drivers_files');

        $this->dropTable('drivers_files');
    }
}
