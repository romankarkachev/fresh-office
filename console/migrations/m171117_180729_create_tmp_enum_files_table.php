<?php

use yii\db\Migration;

/**
 * Создается таблица "Папки хранилища, по которым отработка завершена".
 */
class m171117_180729_create_tmp_enum_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Временная таблица. Папки, по которым отработка завершена"';
        };

        $this->createTable('tmp_enum_files', [
            'id' => $this->primaryKey(),
            'folder_name' => $this->string()->notNull()->comment('Имя папки'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('tmp_enum_files');
    }
}
