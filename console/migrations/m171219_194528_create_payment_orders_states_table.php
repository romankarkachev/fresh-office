<?php

use yii\db\Migration;

/**
 * Создается таблица "Статусы платежных ордеров".
 */
class m171219_194528_create_payment_orders_states_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Статусы платежных ордеров"';
        };

        $this->createTable('payment_orders_states', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('payment_orders_states', [
            'id' => 1,
            'name' => 'Черновик',
        ]);

        $this->insert('payment_orders_states', [
            'id' => 2,
            'name' => 'Согласование',
        ]);

        $this->insert('payment_orders_states', [
            'id' => 3,
            'name' => 'Утвержден',
        ]);

        $this->insert('payment_orders_states', [
            'id' => 4,
            'name' => 'Оплачен',
        ]);

        $this->insert('payment_orders_states', [
            'id' => 5,
            'name' => 'Отказ',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('payment_orders_states');
    }
}
