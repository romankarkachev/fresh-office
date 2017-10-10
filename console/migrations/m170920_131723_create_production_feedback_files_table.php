<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы обратной связи производства".
 */
class m170920_131723_create_production_feedback_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы обратной связи производства"';
        };

        $this->createTable('production_feedback_files', [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            'uploaded_by' => $this->integer()->notNull()->comment('Автор загрузки'),
            'action' => 'TINYINT(1) DEFAULT"1" NOT NULL COMMENT"Признак соответствия груза документам (1 - не соответствует, 2 - соответствует)"',
            'project_id' => $this->integer()->notNull()->comment('Проект'),
            'ca_id' => $this->integer()->notNull()->comment('Контрагент'),
            'thumb_ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу-миниатюре'),
            'thumb_fn' => $this->string(255)->notNull()->comment('Имя файла-миниатюры'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('uploaded_by', 'production_feedback_files', 'uploaded_by');

        $this->addForeignKey('fk_production_feedback_files_uploaded_by', 'production_feedback_files', 'uploaded_by', 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_production_feedback_files_uploaded_by', 'production_feedback_files');

        $this->dropIndex('uploaded_by', 'production_feedback_files');

        $this->dropTable('production_feedback_files');
    }
}
