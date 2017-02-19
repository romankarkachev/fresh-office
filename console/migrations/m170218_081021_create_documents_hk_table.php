<?php

use yii\db\Migration;

/**
 * Создается таблица "Виды обращений, отмеченные в документах".
 */
class m170218_081021_create_documents_hk_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Виды обращений, отмеченные в документах"';
        }

        $this->createTable('documents_hk', [
            'id' => $this->primaryKey(),
            'doc_id' => $this->integer()->notNull()->comment('Документ'),
            'hk_id' => $this->integer()->notNull()->comment('Вид обращения'),
        ], $tableOptions);

        $this->createIndex('doc_id', 'documents_hk', 'doc_id');
        $this->createIndex('hk_id', 'documents_hk', 'hk_id');

        $this->addForeignKey('fk_documents_hk_doc_id', 'documents_hk', 'doc_id', 'documents', 'id');
        $this->addForeignKey('fk_documents_hk_hk_id', 'documents_hk', 'hk_id', 'handling_kinds', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_documents_hk_hk_id', 'documents_hk');
        $this->dropForeignKey('fk_documents_hk_doc_id', 'documents_hk');

        $this->dropIndex('hk_id', 'documents_hk');
        $this->dropIndex('doc_id', 'documents_hk');

        $this->dropTable('documents_hk');
    }
}
