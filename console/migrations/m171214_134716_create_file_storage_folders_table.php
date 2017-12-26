<?php

use yii\db\Migration;

/**
 * Создается таблица "Папки контрагентов в файловом хранилище".
 */
class m171214_134716_create_file_storage_folders_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Папки контрагентов в файловом хранилище"';
        };

        $this->createTable('file_storage_folders', [
            'id' => $this->primaryKey(),
            'fo_ca_id' => $this->integer()->notNull()->comment('ID контрагента из Fresh office'),
            'fo_ca_name' => $this->string()->notNull()->comment('Наименование контрагента из Fresh office'),
            'folder_name' => $this->string()->notNull()->comment('Имя папки'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('file_storage_folders');
    }
}
