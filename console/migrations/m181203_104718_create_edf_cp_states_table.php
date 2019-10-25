<?php

use yii\db\Migration;

/**
 * Создается таблица "Соответствие статусов пакетов корреспонденции статусам электронных документов".
 */
class m181203_104718_create_edf_cp_states_table extends Migration
{
    /**
     * Поля, которые использованы в разных местах
     * В целях рефакторинга имена собраны в одном месте
     */
    const FIELD_CP_ID_NAME = 'cp_id';
    const FIELD_EDF_ID_NAME = 'edf_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля cp_id
     */
    private $fkCpIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля edf_id
     */
    private $fkEdfIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'edf_cp_states';
        $this->fkCpIdName = 'fk_' . $this->tableName . '_' . self::FIELD_CP_ID_NAME;
        $this->fkEdfIdName = 'fk_' . $this->tableName . '_' . self::FIELD_EDF_ID_NAME;

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Соответствие статусов пакетов корреспонденции статусам электронных документов"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_CP_ID_NAME => $this->integer()->notNull()->comment('Пакет корреспонденции'),
            self::FIELD_EDF_ID_NAME => $this->integer()->notNull()->comment('Электронный документ'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CP_ID_NAME, $this->tableName, self::FIELD_CP_ID_NAME);
        $this->createIndex(self::FIELD_EDF_ID_NAME, $this->tableName, self::FIELD_EDF_ID_NAME);

        $this->addForeignKey($this->fkCpIdName, $this->tableName, self::FIELD_CP_ID_NAME, 'correspondence_packages', 'id');
        $this->addForeignKey($this->fkEdfIdName, $this->tableName, self::FIELD_EDF_ID_NAME, 'edf', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkCpIdName, $this->tableName);
        $this->dropForeignKey($this->fkEdfIdName, $this->tableName);

        $this->dropIndex(self::FIELD_CP_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_EDF_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
