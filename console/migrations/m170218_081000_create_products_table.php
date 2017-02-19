<?php

use yii\db\Migration;

/**
 * Создается таблица "Номенклатура".
 */
class m170218_081000_create_products_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Номенклатура"';
        }

        $this->createTable('products', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull()->comment('Наименование'),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'is_deleted' => 'TINYINT(1) DEFAULT 0 NOT NULL COMMENT "0 - активен, 1 - пометка удаления"',
            'author_id' => $this->integer()->notNull()->comment('Автор создания записи'),
            'type' => 'TINYINT(1) DEFAULT 1 NOT NULL COMMENT "0 - отходы, 1 - товары (услуги)"',
            'unit' => $this->string(30)->comment('Единица измерения'),
            'uw' => $this->string(50)->comment('Способ утилизации'),
            'dc' => $this->string(10)->comment('Класс опасности'),
            'fkko' => $this->string(11)->comment('Код по ФККО-2014'),
            'fkko_date' => $this->date()->comment('Дата внесения в ФККО'),
            'fo_id' => $this->integer(5)->comment('Код из Fresh Office'),
            'fo_name' => $this->text()->comment('Наименование из Fresh Office'),
            'fo_fkko' => $this->string(20)->comment('Код ФККО из Fresh Office'),
        ], $tableOptions);

        $this->createIndex('author_id', 'products', 'author_id');

        $this->addForeignKey('fk_products_author_id', 'products', 'author_id', 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_products_author_id', 'products');

        $this->dropIndex('author_id', 'products');

        $this->dropTable('products');
    }
}
