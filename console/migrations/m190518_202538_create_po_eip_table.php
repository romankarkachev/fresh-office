<?php

use yii\db\Migration;

/**
 * Создается таблица "Свойства статей расходов".
 */
class m190518_202538_create_po_eip_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_EXPENDITURE_ITEMS_ID_NAME = 'ei_id';
    const FIELD_PROPERTY_ID_NAME = 'property_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля ei_id
     */
    private $fkEiIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля property_id
     */
    private $fkPropertyIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'po_eip';
        $this->fkEiIdName = 'fk_' . $this->tableName . '_' . self::FIELD_EXPENDITURE_ITEMS_ID_NAME;
        $this->fkPropertyIdName = 'fk_' . $this->tableName . '_' . self::FIELD_PROPERTY_ID_NAME;

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Свойства статей расходов"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_EXPENDITURE_ITEMS_ID_NAME => $this->integer()->notNull()->comment('Статья расходов'),
            self::FIELD_PROPERTY_ID_NAME => $this->integer()->notNull()->comment('Свойство'),
            'is_required' => 'TINYINT NOT NULL DEFAULT"0" COMMENT"Является ли обязательным для заполнения"',
        ], $tableOptions);

        $this->createIndex(self::FIELD_EXPENDITURE_ITEMS_ID_NAME, $this->tableName, self::FIELD_EXPENDITURE_ITEMS_ID_NAME);
        $this->createIndex(self::FIELD_PROPERTY_ID_NAME, $this->tableName, self::FIELD_PROPERTY_ID_NAME);

        $this->addForeignKey($this->fkEiIdName, $this->tableName, self::FIELD_EXPENDITURE_ITEMS_ID_NAME, 'po_ei', 'id');
        $this->addForeignKey($this->fkPropertyIdName, $this->tableName, self::FIELD_PROPERTY_ID_NAME, 'po_properties', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkPropertyIdName, $this->tableName);
        $this->dropForeignKey($this->fkEiIdName, $this->tableName);

        $this->dropIndex(self::FIELD_PROPERTY_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_EXPENDITURE_ITEMS_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
