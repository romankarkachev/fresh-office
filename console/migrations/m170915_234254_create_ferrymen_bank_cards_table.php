<?php

use yii\db\Migration;

/**
 * Создается таблица "Банковские карты перевозчика".
 */
class m170915_234254_create_ferrymen_bank_cards_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Банковские карты перевозчика"';
        };

        $this->createTable('ferrymen_bank_cards', [
            'id' => $this->primaryKey(),
            'ferryman_id' => $this->integer()->notNull()->comment('Перевозчик'),
            'cardholder' => $this->string(100)->comment('Собственник'),
            'number' => $this->string(20)->notNull()->comment('Номер карты'),
            'bank' => $this->string()->comment('Банк, которому принадлежит карта'),
        ], $tableOptions);

        $this->createIndex('ferryman_id', 'ferrymen_bank_cards', 'ferryman_id');

        $this->addForeignKey('fk_ferrymen_bank_cards_ferryman_id', 'ferrymen_bank_cards', 'ferryman_id', 'ferrymen', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_ferrymen_bank_cards_ferryman_id', 'ferrymen_bank_cards');

        $this->dropIndex('ferryman_id', 'ferrymen_bank_cards');

        $this->dropTable('ferrymen_bank_cards');
    }
}
