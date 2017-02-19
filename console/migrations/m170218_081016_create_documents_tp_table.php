<?php

use yii\db\Migration;

/**
 * Создается таблица "Табличные части документов".
 */
class m170218_081016_create_documents_tp_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Табличные части документов"';
        }

        $this->createTable('documents_tp', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'author_id' => $this->integer()->notNull()->comment('Автор создания записи'),
            'doc_id' => $this->integer()->notNull()->comment('Документ'),
            'product_id' => $this->integer()->notNull()->comment('Номенклатура'),
            'quantity' => $this->decimal(15,2)->comment('Количество'),
        ], $tableOptions);

        $this->createIndex('author_id', 'documents_tp', 'author_id');
        $this->createIndex('doc_id', 'documents_tp', 'doc_id');
        $this->createIndex('product_id', 'documents_tp', 'product_id');

        $this->addForeignKey('fk_documents_tp_author_id', 'documents_tp', 'author_id', 'user', 'id');
        $this->addForeignKey('fk_documents_tp_doc_id', 'documents_tp', 'doc_id', 'documents', 'id');
        $this->addForeignKey('fk_documents_tp_product_id', 'documents_tp', 'product_id', 'products', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_documents_tp_product_id', 'documents_tp');
        $this->dropForeignKey('fk_documents_tp_doc_id', 'documents_tp');
        $this->dropForeignKey('fk_documents_tp_author_id', 'documents_tp');

        $this->dropIndex('product_id', 'documents_tp');
        $this->dropIndex('doc_id', 'documents_tp');
        $this->dropIndex('author_id', 'documents_tp');

        $this->dropTable('documents_tp');
    }
}
