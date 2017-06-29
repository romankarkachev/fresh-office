<?php

use yii\db\Migration;

/**
 * Создается таблица "Условия оплаты".
 */
class m170603_165934_create_payment_conditions_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Условия оплаты"';
        };

        $this->createTable('payment_conditions', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('payment_conditions', [
            'id' => 1,
            'name' => 'Предоплата',
        ]);

        $this->insert('payment_conditions', [
            'id' => 2,
            'name' => 'Постоплата',
        ]);

        $this->insert('payment_conditions', [
            'id' => 3,
            'name' => 'Частями',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('payment_conditions');
    }
}
