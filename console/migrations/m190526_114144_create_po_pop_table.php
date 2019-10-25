<?php

use yii\db\Migration;

/**
 * Создается таблица "Свойства статей платежных ордеров".
 */
class m190526_114144_create_po_pop_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_PO_ID_NAME = 'po_id';
    const FIELD_EI_ID_NAME = 'ei_id';
    const FIELD_PROPERTY_ID_NAME = 'property_id';
    const FIELD_VALUE_ID_NAME = 'value_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля po_id
     */
    private $fkPoIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля ei_id
     */
    private $fkEiIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля property_id
     */
    private $fkPropertyIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля value_id
     */
    private $fkValueIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'po_pop';
        $this->fkPoIdName = 'fk_' . $this->tableName . '_' . self::FIELD_PO_ID_NAME;
        $this->fkEiIdName = 'fk_' . $this->tableName . '_' . self::FIELD_EI_ID_NAME;
        $this->fkPropertyIdName = 'fk_' . $this->tableName . '_' . self::FIELD_PROPERTY_ID_NAME;
        $this->fkValueIdName = 'fk_' . $this->tableName . '_' . self::FIELD_VALUE_ID_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Свойства статей платежных ордеров"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_PO_ID_NAME => $this->integer()->notNull()->comment('Платежный ордер'),
            self::FIELD_EI_ID_NAME => $this->integer()->notNull()->comment('Статья расходов'),
            self::FIELD_PROPERTY_ID_NAME => $this->integer()->notNull()->comment('Свойство'),
            self::FIELD_VALUE_ID_NAME => $this->integer()->notNull()->comment('Значение свойства'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_PO_ID_NAME, $this->tableName, self::FIELD_PO_ID_NAME);
        $this->createIndex(self::FIELD_EI_ID_NAME, $this->tableName, self::FIELD_EI_ID_NAME);
        $this->createIndex(self::FIELD_PROPERTY_ID_NAME, $this->tableName, self::FIELD_PROPERTY_ID_NAME);
        $this->createIndex(self::FIELD_VALUE_ID_NAME, $this->tableName, self::FIELD_VALUE_ID_NAME);

        $this->addForeignKey($this->fkPoIdName, $this->tableName, self::FIELD_PO_ID_NAME, 'po', 'id');
        $this->addForeignKey($this->fkEiIdName, $this->tableName, self::FIELD_EI_ID_NAME, 'po_ei', 'id');
        $this->addForeignKey($this->fkPropertyIdName, $this->tableName, self::FIELD_PROPERTY_ID_NAME, 'po_properties', 'id');
        $this->addForeignKey($this->fkValueIdName, $this->tableName, self::FIELD_VALUE_ID_NAME, 'po_values', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkValueIdName, $this->tableName);
        $this->dropForeignKey($this->fkPropertyIdName, $this->tableName);
        $this->dropForeignKey($this->fkEiIdName, $this->tableName);
        $this->dropForeignKey($this->fkPoIdName, $this->tableName);

        $this->dropIndex(self::FIELD_VALUE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_PROPERTY_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_EI_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_PO_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
