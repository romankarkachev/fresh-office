<?php

use yii\db\Migration;

/**
 * Создается таблица "Перевозчики".
 */
class m170603_170831_create_ferrymen_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Перевозчики"';
        };

        $this->createTable('ferrymen', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->comment('Наименование'),
            'ft_id' => $this->integer()->notNull()->comment('Тип'),
            'pc_id' => $this->integer()->notNull()->comment('Условия оплаты'),
            'phone' => $this->string(50)->comment('Телефоны'),
            'email' => $this->string()->comment('E-mail'),
            'contact_person' => $this->string(50)->comment('Контактное лицо'),
        ], $tableOptions);

        $this->createIndex('ft_id', 'ferrymen', 'ft_id');
        $this->createIndex('pc_id', 'ferrymen', 'pc_id');

        $this->addForeignKey('fk_ferrymen_ft_id', 'ferrymen', 'ft_id', 'ferrymen_types', 'id');
        $this->addForeignKey('fk_ferrymen_pc_id', 'ferrymen', 'pc_id', 'payment_conditions', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_ferrymen_pc_id', 'ferrymen');
        $this->dropForeignKey('fk_ferrymen_ft_id', 'ferrymen');

        $this->dropIndex('pc_id', 'ferrymen');
        $this->dropIndex('ft_id', 'ferrymen');

        $this->dropTable('ferrymen');
    }
}
