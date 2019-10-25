<?php

use yii\db\Migration;

/**
 * Создается таблица "Табличная часть электронного документа".
 */
class m181021_120401_create_edf_tp_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_ED_ID_NAME = 'ed_id';
    const FIELD_FKKO_ID_NAME = 'fkko_id';
    const FIELD_UNIT_ID_NAME = 'unit_id';
    const FIELD_HK_ID_NAME = 'hk_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля ed_id
     */
    private $fkEdIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля fkko_id
     */
    private $fkFkkoIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля unit_id
     */
    private $fkUnitIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля hk_id
     */
    private $fkHkIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'edf_tp';
        $this->fkEdIdName = 'fk_' . $this->tableName . '_' . self::FIELD_ED_ID_NAME;
        $this->fkFkkoIdName = 'fk_' . $this->tableName . '_' . self::FIELD_FKKO_ID_NAME;
        $this->fkUnitIdName = 'fk_' . $this->tableName . '_' . self::FIELD_UNIT_ID_NAME;
        $this->fkHkIdName = 'fk_' . $this->tableName . '_' . self::FIELD_HK_ID_NAME;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Табличная часть электронного документа"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_ED_ID_NAME => $this->integer()->notNull()->comment('Электронный документ'),
            self::FIELD_FKKO_ID_NAME => $this->integer()->comment('Код ФККО'),
            'fkko_name' => $this->string()->comment('ФККО'),
            self::FIELD_UNIT_ID_NAME => $this->integer()->comment('Единица измерения'),
            'measure' => $this->decimal(12,2)->comment('Количество'),
            self::FIELD_HK_ID_NAME => $this->integer()->notNull()->comment('Вид обращения'),
            'price' => $this->decimal(12,2)->comment('Стоимость'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_ED_ID_NAME, $this->tableName, self::FIELD_ED_ID_NAME);
        $this->createIndex(self::FIELD_FKKO_ID_NAME, $this->tableName, self::FIELD_FKKO_ID_NAME);
        $this->createIndex(self::FIELD_UNIT_ID_NAME, $this->tableName, self::FIELD_UNIT_ID_NAME);
        $this->createIndex(self::FIELD_HK_ID_NAME, $this->tableName, self::FIELD_HK_ID_NAME);

        $this->addForeignKey($this->fkEdIdName, $this->tableName, self::FIELD_ED_ID_NAME, 'edf', 'id');
        $this->addForeignKey($this->fkFkkoIdName, $this->tableName, self::FIELD_FKKO_ID_NAME, 'fkko', 'id');
        $this->addForeignKey($this->fkUnitIdName, $this->tableName, self::FIELD_UNIT_ID_NAME, 'units', 'id');
        $this->addForeignKey($this->fkHkIdName, $this->tableName, self::FIELD_HK_ID_NAME, 'handling_kinds', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey($this->fkHkIdName, $this->tableName);
        $this->dropForeignKey($this->fkUnitIdName, $this->tableName);
        $this->dropForeignKey($this->fkFkkoIdName, $this->tableName);
        $this->dropForeignKey($this->fkEdIdName, $this->tableName);

        $this->dropIndex(self::FIELD_HK_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_UNIT_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_FKKO_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_ED_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
