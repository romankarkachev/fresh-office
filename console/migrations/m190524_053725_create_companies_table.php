<?php

use yii\db\Migration;

/**
 * Создается таблица "Контрагенты".
 */
class m190524_053725_create_companies_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля created_by
     */
    private $fkCreatedByName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'companies';
        $this->fkCreatedByName = 'fk_' . $this->tableName . '_' . self::FIELD_CREATED_BY_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Контрагенты"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->notNull()->comment('Автор создания'),
            'guid' => $this->string(36)->unique()->comment('GUID'),
            'name' => $this->string()->notNull()->comment('Наименование'),
            'name_full' => $this->string()->comment('Наименование полное'),
            'name_short' => $this->string()->comment('Наименование сокращенное'),
            'inn' => $this->string(12)->comment('ИНН'),
            'kpp' => $this->string(9)->comment('КПП'),
            'ogrn' => $this->string(15)->comment('ОГРН(ИП)'),
            'address_j' => $this->string()->comment('Юридический адрес контрагента'),
            'address_f' => $this->string()->comment('Фактический адрес контрагента'),
            'dir_post' => $this->string()->comment('Должность директора контрагента (им. падеж)'),
            'dir_name' => $this->string()->comment('ФИО директора контрагента полностью (им. падеж)'),
            'dir_name_of' => $this->string()->comment('ФИО директора контрагента полностью (род. падеж)'),
            'dir_name_short' => $this->string()->comment('ФИО директора контрагента сокрщенно (им. падеж)'),
            'dir_name_short_of' => $this->string()->comment('ФИО директора контрагента сокращенно (род. падеж)'),
            'comment' => $this->text()->comment('Комментарий'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, $this->tableName, self::FIELD_CREATED_BY_NAME);

        $this->addForeignKey($this->fkCreatedByName, $this->tableName, self::FIELD_CREATED_BY_NAME, 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkCreatedByName, $this->tableName);

        $this->dropIndex(self::FIELD_CREATED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
