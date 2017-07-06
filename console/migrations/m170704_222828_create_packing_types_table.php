<?php

use yii\db\Migration;

/**
 * Создается таблица "Виды упаковки".
 */
class m170704_222828_create_packing_types_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Виды упаковки"';
        };

        $this->createTable('packing_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->comment('Наименование'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('packing_types');
    }
}
