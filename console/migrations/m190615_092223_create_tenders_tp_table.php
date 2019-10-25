<?php

use yii\db\Migration;

/**
 * Создается таблица "Табличная часть тендера".
 */
class m190615_092223_create_tenders_tp_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_TENDER_ID_NAME = 'tender_id';
    const FIELD_FKKO_ID_NAME = 'fkko_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля tender_id
     */
    private $fkTenderIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля fkko_id
     */
    private $fkFkkoIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'tenders_tp';
        $this->fkTenderIdName = 'fk_' . $this->tableName . '_' . self::FIELD_TENDER_ID_NAME;
        $this->fkFkkoIdName = 'fk_' . $this->tableName . '_' . self::FIELD_FKKO_ID_NAME;

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Табличная часть тендера"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_TENDER_ID_NAME => $this->integer()->notNull()->comment('Тендер'),
            self::FIELD_FKKO_ID_NAME => $this->integer()->comment('Код ФККО'),
            'fkko_name' => $this->string()->comment('ФККО'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_TENDER_ID_NAME, $this->tableName, self::FIELD_TENDER_ID_NAME);
        $this->createIndex(self::FIELD_FKKO_ID_NAME, $this->tableName, self::FIELD_FKKO_ID_NAME);

        $this->addForeignKey($this->fkTenderIdName, $this->tableName, self::FIELD_TENDER_ID_NAME, 'tenders', 'id');
        $this->addForeignKey($this->fkFkkoIdName, $this->tableName, self::FIELD_FKKO_ID_NAME, 'fkko', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkFkkoIdName, $this->tableName);
        $this->dropForeignKey($this->fkTenderIdName, $this->tableName);

        $this->dropIndex(self::FIELD_FKKO_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_TENDER_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
