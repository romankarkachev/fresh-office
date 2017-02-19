<?php

use yii\db\Migration;

/**
 * Создается таблица "Документы".
 */
class m170218_081011_create_documents_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Документы"';
        }

        $this->createTable('documents', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'author_id' => $this->integer()->notNull()->comment('Автор создания записи'),
            'doc_date' => $this->date()->comment('Дата документа'),
            'fo_project' => $this->integer()->unsigned()->comment('ID проекта во Fresh Office'),
            'fo_customer' => $this->integer()->unsigned()->comment('ID заказчика во Fresh Office'),
            'fo_contract' => $this->integer()->unsigned()->comment('ID договора во Fresh Office'),
            'comment' => $this->text()->comment('Примечание'),
        ], $tableOptions);

        $this->createIndex('author_id', 'documents', 'author_id');

        $this->addForeignKey('fk_documents_author_id', 'documents', 'author_id', 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_documents_author_id', 'documents');

        $this->dropIndex('author_id', 'documents');

        $this->dropTable('documents');
    }
}
