<?php

use yii\db\Migration;

/**
 * Создается таблица "Типы подгружаемых документов".
 */
class m171108_091015_create_uploading_files_meanings_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Типы подгружаемых документов"';
        };

        $this->createTable('uploading_files_meanings', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->comment('Наименование'),
            'keywords' => $this->text()->comment('Ключевые слова, по которым можно понять, что это именно тот самый тип'),
        ], $tableOptions);

        $this->insert('uploading_files_meanings', [
            'id' => 1,
            'name' => 'Договор',
        ]);

        $this->insert('uploading_files_meanings', [
            'id' => 2,
            'name' => 'ТТН',
        ]);

        $this->insert('uploading_files_meanings', [
            'id' => 3,
            'name' => 'Учредительные документы',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('uploading_files_meanings');
    }
}
