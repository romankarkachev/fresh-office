<?php

use yii\db\Migration;

/**
 * Создается таблица "Исключения номенклатуры".
 */
class m170222_091631_create_products_excludes_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Исключения номенклатуры"';
        }

        $this->createTable('products_excludes', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->notNull()->comment('Часть наименования'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('products_excludes');
    }
}
