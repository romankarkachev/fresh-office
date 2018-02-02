<?php

use yii\db\Migration;

/**
 * Создается таблица "История изменений статусов в пакетах корреспонденции".
 */
class m180130_160427_create_correspondence_packages_history_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "История изменений статусов в пакетах корреспонденции"';
        };

        $this->createTable('correspondence_packages_history', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'created_by' => $this->integer()->comment('Автор создания'),
            'cp_id' => $this->integer()->notNull()->comment('Пакет корреспонденции'),
            'description' => $this->text()->comment('Суть события'),
        ], $tableOptions);

        $this->createIndex('created_by', 'correspondence_packages_history', 'created_by');
        $this->createIndex('cp_id', 'correspondence_packages_history', 'cp_id');

        $this->addForeignKey('fk_correspondence_packages_history_created_by', 'correspondence_packages_history', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_correspondence_packages_history_cp_id', 'correspondence_packages_history', 'cp_id', 'correspondence_packages', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_correspondence_packages_history_cp_id', 'correspondence_packages_history');
        $this->dropForeignKey('fk_correspondence_packages_history_created_by', 'correspondence_packages_history');

        $this->dropIndex('cp_id', 'correspondence_packages_history');
        $this->dropIndex('created_by', 'correspondence_packages_history');

        $this->dropTable('correspondence_packages_history');
    }
}
