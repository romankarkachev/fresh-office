<?php

use yii\db\Migration;

/**
 * Создается таблица "Диалоги в запросе на транспорт".
 */
class m170710_123511_create_transport_requests_dialogs_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Диалоги в запросе на транспорт"';
        };

        $this->createTable('transport_requests_dialogs', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'created_by' => $this->integer()->notNull()->comment('Автор создания'),
            'tr_id' => $this->integer()->notNull()->comment('Запрос на транспорт'),
            'message' => $this->text()->notNull()->comment('Текст сообщения'),
        ], $tableOptions);

        $this->createIndex('created_by', 'transport_requests_dialogs', 'created_by');
        $this->createIndex('tr_id', 'transport_requests_dialogs', 'tr_id');

        $this->addForeignKey('fk_transport_requests_dialogs_created_by', 'transport_requests_dialogs', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_transport_requests_dialogs_tr_id', 'transport_requests_dialogs', 'tr_id', 'transport_requests', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_transport_requests_dialogs_tr_id', 'transport_requests_dialogs');
        $this->dropForeignKey('fk_transport_requests_dialogs_created_by', 'transport_requests_dialogs');

        $this->dropIndex('tr_id', 'transport_requests_dialogs');
        $this->dropIndex('created_by', 'transport_requests_dialogs');

        $this->dropTable('transport_requests_dialogs');
    }
}
