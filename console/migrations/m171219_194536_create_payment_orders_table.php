<?php

use yii\db\Migration;

/**
 * Создается таблица "Платежные ордеры".
 */
class m171219_194536_create_payment_orders_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Платежные ордеры"';
        };

        $this->createTable('payment_orders', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'created_by' => $this->integer()->notNull()->comment('Автор создания'),
            'state_id' => $this->integer()->notNull()->comment('Статус'),
            'ferryman_id' => $this->integer()->notNull()->comment('Перевозчик'),
            'projects' => $this->text()->comment('Проекты'),
            'pd_type' => 'TINYINT(1) COMMENT"Payment destination (1 - банковский счет, 2 - перевод на карту)"',
            'pd_id' => $this->integer()->comment('Ссылка на банковский счет (номер карты)'),
            'comment' => $this->text()->comment('Комментарий'),
        ], $tableOptions);

        $this->createIndex('created_by', 'payment_orders', 'created_by');
        $this->createIndex('state_id', 'payment_orders', 'state_id');
        $this->createIndex('ferryman_id', 'payment_orders', 'ferryman_id');

        $this->addForeignKey('fk_payment_orders_created_by', 'payment_orders', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_payment_orders_state_id', 'payment_orders', 'state_id', 'payment_orders_states', 'id');
        $this->addForeignKey('fk_payment_orders_ferryman_id', 'payment_orders', 'ferryman_id', 'ferrymen', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_payment_orders_created_by', 'payment_orders');
        $this->dropForeignKey('fk_payment_orders_state_id', 'payment_orders');
        $this->dropForeignKey('fk_payment_orders_ferryman_id', 'payment_orders');

        $this->dropIndex('ferryman_id', 'payment_orders');
        $this->dropIndex('state_id', 'payment_orders');
        $this->dropIndex('created_by', 'payment_orders');

        $this->dropTable('payment_orders');
    }
}
