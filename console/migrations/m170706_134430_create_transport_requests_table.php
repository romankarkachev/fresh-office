<?php

use yii\db\Migration;

/**
 * Создается таблица "Запросы на транспорт".
 */
class m170706_134430_create_transport_requests_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Запросы на транспорт"';
        };

        $this->createTable('transport_requests', [
            'id' => $this->primaryKey(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('transport_requests');
    }
}
