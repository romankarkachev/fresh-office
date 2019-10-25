<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы бюджетных платежных ордеров".
 */
class m190526_113507_create_po_files_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_UPLOADED_BY_NAME = 'uploaded_by';
    const FIELD_PO_ID_NAME = 'po_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля uploaded_by
     */
    private $fkUploadedByName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля po_id
     */
    private $fkPoIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'po_files';
        $this->fkUploadedByName = 'fk_' . $this->tableName . '_' . self::FIELD_UPLOADED_BY_NAME;
        $this->fkPoIdName = 'fk_' . $this->tableName . '_' . self::FIELD_PO_ID_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы бюджетных платежных ордеров"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            self::FIELD_UPLOADED_BY_NAME => $this->integer()->notNull()->comment('Автор загрузки'),
            self::FIELD_PO_ID_NAME => $this->integer()->notNull()->comment('Платежный ордер'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_UPLOADED_BY_NAME, $this->tableName, self::FIELD_UPLOADED_BY_NAME);
        $this->createIndex(self::FIELD_PO_ID_NAME, $this->tableName, self::FIELD_PO_ID_NAME);

        $this->addForeignKey($this->fkUploadedByName, $this->tableName, self::FIELD_UPLOADED_BY_NAME, 'user', 'id');
        $this->addForeignKey($this->fkPoIdName, $this->tableName, self::FIELD_PO_ID_NAME, 'po', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkPoIdName, $this->tableName);
        $this->dropForeignKey($this->fkUploadedByName, $this->tableName);

        $this->dropIndex(self::FIELD_PO_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_UPLOADED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
