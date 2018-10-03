<?php

use yii\db\Migration;

/**
 * Создается таблица "Проекты по экологии".
 */
class m180929_194915_create_eco_projects_table extends Migration
{
    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_CREATED_BY_NAME = 'created_by';

    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_TYPE_ID_NAME = 'type_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля created_by
     */
    private $fkCreatedByName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля type_id
     */
    private $fkTypeIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'eco_projects';
        $this->fkCreatedByName = 'fk_' . $this->tableName . '_' . self::FIELD_CREATED_BY_NAME;
        $this->fkTypeIdName = 'fk_' . $this->tableName . '_' . self::FIELD_TYPE_ID_NAME;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Проекты по экологии"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->comment('Автор создания'),
            self::FIELD_TYPE_ID_NAME => $this->integer()->notNull()->comment('Тип проекта'),
            'ca_id' => $this->integer()->notNull()->comment('Заказчик'),
            'date_start' => $this->date()->comment('Дата запуска проекта в работу'),
            'date_close_plan' => $this->date()->comment('Планируемая дата завершения проекта'),
            'closed_at' => $this->integer()->comment('Фактическая дата завершения проекта'),
            'comment' => $this->text()->comment('Примечание'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, $this->tableName, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_TYPE_ID_NAME, $this->tableName, self::FIELD_TYPE_ID_NAME);

        $this->addForeignKey($this->fkCreatedByName, $this->tableName, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey($this->fkTypeIdName, $this->tableName, self::FIELD_TYPE_ID_NAME, 'eco_types', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey($this->fkTypeIdName, $this->tableName);
        $this->dropForeignKey($this->fkCreatedByName, $this->tableName);

        $this->dropIndex(self::FIELD_TYPE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
