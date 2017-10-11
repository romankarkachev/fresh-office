<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы лицензий организаций".
 */
class m171011_062941_create_licenses_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы лицензий организаций"';
        };

        $this->createTable('licenses_files', [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            'uploaded_by' => $this->integer()->notNull()->comment('Автор загрузки'),
            'organization_id' => $this->integer()->notNull()->comment('Организация'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('uploaded_by', 'licenses_files', 'uploaded_by');
        $this->createIndex('organization_id', 'licenses_files', 'organization_id');

        $this->addForeignKey('fk_licenses_files_uploaded_by', 'licenses_files', 'uploaded_by', 'user', 'id');
        $this->addForeignKey('fk_licenses_files_organization_id', 'licenses_files', 'organization_id', 'organizations', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_licenses_files_organization_id', 'licenses_files');
        $this->dropForeignKey('fk_licenses_files_uploaded_by', 'licenses_files');

        $this->dropIndex('organization_id', 'licenses_files');
        $this->dropIndex('uploaded_by', 'licenses_files');

        $this->dropTable('licenses_files');
    }
}
