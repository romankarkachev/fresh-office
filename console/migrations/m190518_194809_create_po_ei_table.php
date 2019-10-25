<?php

use yii\db\Migration;

/**
 * Создается таблица "Статьи расходов в платежных ордерах" (payment orders expenditure items).
 */
class m190518_194809_create_po_ei_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_GROUP_ID_NAME = 'group_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля group_id
     */
    private $fkGroupIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'po_ei';
        $this->fkGroupIdName = 'fk_' . $this->tableName . '_' . self::FIELD_GROUP_ID_NAME;

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Статьи расходов в платежных ордерах"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_GROUP_ID_NAME => $this->integer()->notNull()->comment('Группа статей'),
            'name' => $this->string()->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_GROUP_ID_NAME, $this->tableName, self::FIELD_GROUP_ID_NAME);

        $this->addForeignKey($this->fkGroupIdName, $this->tableName, self::FIELD_GROUP_ID_NAME, 'po_eig', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkGroupIdName, $this->tableName);

        $this->dropIndex(self::FIELD_GROUP_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
