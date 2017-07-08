<?php

use yii\db\Migration;

/**
 * Создается таблица "Табличная часть Транспорт к запросам".
 */
class m170706_191030_create_transport_requests_transport_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Табличная часть Транспорт к запросам"';
        };

        $this->createTable('transport_requests_transport', [
            'id' => $this->primaryKey(),
            'tr_id' => $this->integer()->notNull()->comment('Запрос на транспорт'),
            'tt_id' => $this->integer()->notNull()->comment('Тип техники'),
            'amount' => $this->decimal(12,2)->comment('Стоимость'),
        ], $tableOptions);

        $this->createIndex('tr_id', 'transport_requests_transport', 'tr_id');
        $this->createIndex('tt_id', 'transport_requests_transport', 'tt_id');

        $this->addForeignKey('fk_transport_requests_transport_tr_id', 'transport_requests_transport', 'tr_id', 'transport_requests', 'id');
        $this->addForeignKey('fk_transport_requests_transport_tt_id', 'transport_requests_transport', 'tt_id', 'transport_types', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_transport_requests_transport_tt_id', 'transport_requests_transport');
        $this->dropForeignKey('fk_transport_requests_transport_tr_id', 'transport_requests_transport');

        $this->dropIndex('tt_id', 'transport_requests_transport');
        $this->dropIndex('tr_id', 'transport_requests_transport');

        $this->dropTable('transport_requests_transport');
    }
}
