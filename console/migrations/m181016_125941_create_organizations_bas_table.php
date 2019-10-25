<?php

use yii\db\Migration;

/**
 * Создается таблица "Банковские счета организаций".
 */
class m181016_125941_create_organizations_bas_table extends Migration
{
    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_ORG_ID_NAME = 'org_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля type_id
     */
    private $fkOrgIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'organizations_bas';
        $this->fkOrgIdName = 'fk_' . $this->tableName . '_' . self::FIELD_ORG_ID_NAME;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_ORG_ID_NAME => $this->integer()->notNull()->comment('Организация'),
            'bank_an' => $this->string(25)->notNull()->comment('Номер р/с'),
            'bank_bik' => $this->string(10)->notNull()->comment('БИК банка'),
            'bank_name' => $this->string()->notNull()->comment('Наименование банка'),
            'bank_ca' => $this->string(25)->notNull()->comment('Корр. счет'),
        ]);

        $this->createIndex(self::FIELD_ORG_ID_NAME, $this->tableName, self::FIELD_ORG_ID_NAME);

        $this->addForeignKey($this->fkOrgIdName, $this->tableName, self::FIELD_ORG_ID_NAME, 'organizations', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey($this->fkOrgIdName, $this->tableName);

        $this->dropIndex(self::FIELD_ORG_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
