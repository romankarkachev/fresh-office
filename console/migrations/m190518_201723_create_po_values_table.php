<?php

use yii\db\Migration;

/**
 * Создается таблица "Значения свойств статей расходов".
 */
class m190518_201723_create_po_values_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_PROPERTY_ID_NAME = 'property_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля property_id
     */
    private $fkPropertyIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'po_values';
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
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Значения свойств статей расходов"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_PROPERTY_ID_NAME => $this->integer()->notNull()->comment('Свойство'),
            'name' => $this->string()->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_PROPERTY_ID_NAME, $this->tableName, self::FIELD_PROPERTY_ID_NAME);

        $this->addForeignKey($this->fkPropertyIdName, $this->tableName, self::FIELD_PROPERTY_ID_NAME, 'po_properties', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkPropertyIdName, $this->tableName);

        $this->dropIndex(self::FIELD_PROPERTY_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
