<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы платежных ордеров".
 */
class m171219_194548_create_payment_orders_files_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы платежных ордеров"';
        };

        $this->createTable('payment_orders_files', [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            'uploaded_by' => $this->integer()->notNull()->comment('Автор загрузки'),
            'po_id' => $this->integer()->notNull()->comment('Платежный ордер'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex('uploaded_by', 'payment_orders_files', 'uploaded_by');
        $this->createIndex('po_id', 'payment_orders_files', 'po_id');

        $this->addForeignKey('fk_payment_orders_files_uploaded_by', 'payment_orders_files', 'uploaded_by', 'user', 'id');
        $this->addForeignKey('fk_payment_orders_files_po_id', 'payment_orders_files', 'po_id', 'payment_orders', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_payment_orders_files_po_id', 'payment_orders_files');
        $this->dropForeignKey('fk_payment_orders_files_uploaded_by', 'payment_orders_files');

        $this->dropIndex('po_id', 'payment_orders_files');
        $this->dropIndex('uploaded_by', 'payment_orders_files');

        $this->dropTable('payment_orders_files');
    }
}
