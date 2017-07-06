<?php

use yii\db\Migration;

/**
 * Создается таблица "Статусы запросов транспорта".
 */
class m170704_223043_create_transport_requests_states_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Статусы запросов транспорта"';
        };

        $this->createTable('transport_requests_states', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->comment('Наименование'),
        ], $tableOptions);

        $this->insert('transport_requests_states', [
            'id' => 1,
            'name' => 'Новый',
        ]);

        $this->insert('transport_requests_states', [
            'id' => 2,
            'name' => 'В обработке',
        ]);

        $this->insert('transport_requests_states', [
            'id' => 3,
            'name' => 'Закрыт',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('transport_requests_states');
    }
}
