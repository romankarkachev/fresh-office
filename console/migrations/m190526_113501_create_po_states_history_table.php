<?php

use yii\db\Migration;

/**
 * Создается таблица "История изменения статусов бюджетных платежных ордеров".
 */
class m190526_113501_create_po_states_history_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';
    const FIELD_PO_ID_NAME = 'po_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля created_by
     */
    private $fkCreatedByName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля po_id
     */
    private $fkPoIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'po_states_history';
        $this->fkCreatedByName = 'fk_' . $this->tableName . '_' . self::FIELD_CREATED_BY_NAME;
        $this->fkPoIdName = 'fk_' . $this->tableName . '_' . self::FIELD_PO_ID_NAME;

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "История изменения статусов бюджетных платежных ордеров"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->comment('Автор создания'),
            self::FIELD_PO_ID_NAME => $this->integer()->notNull()->comment('Платежный ордер'),
            'description' => $this->text()->comment('Суть события'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, $this->tableName, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_PO_ID_NAME, $this->tableName, self::FIELD_PO_ID_NAME);

        $this->addForeignKey($this->fkCreatedByName, $this->tableName, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey($this->fkPoIdName, $this->tableName, self::FIELD_PO_ID_NAME, 'po', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkPoIdName, $this->tableName);
        $this->dropForeignKey($this->fkCreatedByName, $this->tableName);

        $this->dropIndex(self::FIELD_PO_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
