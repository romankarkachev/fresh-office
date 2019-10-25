<?php

use yii\db\Migration;

/**
 * Создается таблица "Файлы к электронным документам".
 */
class m181021_120410_create_edf_files_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_UPLOADED_BY_NAME = 'uploaded_by';
    const FIELD_ED_ID_NAME = 'ed_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля uploaded_by
     */
    private $fkUploadedByName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля ed_id
     */
    private $fkEdIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'edf_files';
        $this->fkUploadedByName = 'fk_' . $this->tableName . '_' . self::FIELD_UPLOADED_BY_NAME;
        $this->fkEdIdName = 'fk_' . $this->tableName . '_' . self::FIELD_ED_ID_NAME;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Файлы к электронным документам"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'uploaded_at' => $this->integer()->notNull()->comment('Дата и время загрузки'),
            self::FIELD_UPLOADED_BY_NAME => $this->integer()->notNull()->comment('Автор загрузки'),
            self::FIELD_ED_ID_NAME => $this->integer()->notNull()->comment('Электронный документ'),
            'ffp' => $this->string(255)->notNull()->comment('Полный путь к файлу'),
            'fn' => $this->string(255)->notNull()->comment('Имя файла'),
            'ofn' => $this->string(255)->notNull()->comment('Оригинальное имя файла'),
            'size' => $this->integer()->comment('Размер файла'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_UPLOADED_BY_NAME, $this->tableName, self::FIELD_UPLOADED_BY_NAME);
        $this->createIndex(self::FIELD_ED_ID_NAME, $this->tableName, self::FIELD_ED_ID_NAME);

        $this->addForeignKey($this->fkUploadedByName, $this->tableName, self::FIELD_UPLOADED_BY_NAME, 'user', 'id');
        $this->addForeignKey($this->fkEdIdName, $this->tableName, self::FIELD_ED_ID_NAME, 'edf', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey($this->fkEdIdName, $this->tableName);
        $this->dropForeignKey($this->fkUploadedByName, $this->tableName);

        $this->dropIndex(self::FIELD_ED_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_UPLOADED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
