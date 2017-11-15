<?php

use yii\db\Migration;

/**
 * Создается таблица "Запросы сканов лицензий".
 */
class m171011_063058_create_licenses_requests_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Запросы сканов лицензий"';
        };

        $this->createTable('licenses_requests', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'created_by' => $this->integer()->notNull()->comment('Автор создания'),
            'state_id' => $this->integer()->notNull()->comment('Статус запроса'),
            'ca_email' => $this->string()->notNull()->comment('E-mail контрагента'),
            'ca_name' => $this->string()->comment('Контрагент'),
            'ca_id' => $this->integer()->comment('Контрагент'),
            'comment' => $this->text()->comment('Примечание'),
        ], $tableOptions);

        $this->createIndex('created_by', 'licenses_requests', 'created_by');
        $this->createIndex('state_id', 'licenses_requests', 'state_id');

        $this->addForeignKey('fk_licenses_requests_created_by', 'licenses_requests', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_licenses_requests_state_id', 'licenses_requests', 'state_id', 'licenses_requests_states', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_licenses_requests_state_id', 'licenses_requests');
        $this->dropForeignKey('fk_licenses_requests_created_by', 'licenses_requests');

        $this->dropIndex('state_id', 'licenses_requests');
        $this->dropIndex('created_by', 'licenses_requests');

        $this->dropTable('licenses_requests');
    }
}
