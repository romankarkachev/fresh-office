<?php

use yii\db\Migration;

/**
 * Создается таблица "Статусы запросов лицензий".
 */
class m171011_062925_create_licenses_requests_states_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Статусы запросов лицензий"';
        };

        $this->createTable('licenses_requests_states', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('licenses_requests_states', [
            'id' => 1,
            'name' => 'Новый',
        ]);

        $this->insert('licenses_requests_states', [
            'id' => 2,
            'name' => 'Одобрен',
        ]);

        $this->insert('licenses_requests_states', [
            'id' => 3,
            'name' => 'Отказ',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('licenses_requests_states');
    }
}
