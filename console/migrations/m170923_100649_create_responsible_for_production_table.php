<?php

use yii\db\Migration;

/**
 * Создается таблица "Ответственные для производства".
 */
class m170923_100649_create_responsible_for_production_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Ответственные для производства"';
        };

        $this->createTable('responsible_for_production', [
            'id' => $this->primaryKey(),
            'type' => 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Тип получателя (1 - всегда, 2 - при несовпадении)"',
            'receiver' => $this->string()->comment('E-mail'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('responsible_for_production');
    }
}
