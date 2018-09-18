<?php

use yii\db\Migration;

/**
 * Создается таблица "Приаттаченные к письмам файлы".
 */
class m180517_132643_create_ce_attached_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Приаттаченные к письмам файлы"';
        };

        $this->createTable('ce_attached_files', [
            'id' => $this->primaryKey(),
            'message_id' => $this->integer()->notNull()->comment('Письмо'),
            'ofn' => $this->string()->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('message_id', 'ce_attached_files', 'message_id');

        $this->addForeignKey('fk_ce_attached_files_message_id', 'ce_attached_files', 'message_id', 'ce_messages', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_ce_attached_files_message_id', 'ce_attached_files');

        $this->dropIndex('message_id', 'ce_attached_files');

        $this->dropTable('ce_attached_files');
    }
}
