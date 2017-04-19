<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы к обращениям".
 */
class m170418_062508_create_appeals_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы к обращениям"';
        };

        $this->createTable('appeals_files', [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            'sent_at' => $this->integer()->comment('Дата и время отправки по E-mail'),
            'appeal_id' => $this->integer()->notNull()->comment('Обращение'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('appeal_id', 'appeals_files', 'appeal_id');

        $this->addForeignKey('fk_appeals_files_appeal_id', 'appeals_files', 'appeal_id', 'appeals', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_appeals_files_appeal_id', 'appeals_files');

        $this->dropIndex('appeal_id', 'appeals_files');

        $this->dropTable('appeals_files');
    }
}
