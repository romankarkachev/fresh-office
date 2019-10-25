<?php

use yii\db\Migration;

/**
 * Создается таблица "Банковские счета контрагентов".
 */
class m190524_053731_create_companies_bank_accounts_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_COMPANY_ID_NAME = 'company_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля company_id
     */
    private $fkCompanyIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'companies_bank_accounts';
        $this->fkCompanyIdName = 'fk_' . $this->tableName . '_' . self::FIELD_COMPANY_ID_NAME;

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Банковские счета контрагентов"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_COMPANY_ID_NAME => $this->integer()->notNull()->comment('Контрагент'),
            'bank_an' => $this->string(25)->notNull()->comment('Номер р/с'),
            'bank_bik' => $this->string(10)->notNull()->comment('БИК банка'),
            'bank_name' => $this->string()->notNull()->comment('Наименование банка'),
            'bank_ca' => $this->string(25)->notNull()->comment('Корр. счет'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_COMPANY_ID_NAME, $this->tableName, self::FIELD_COMPANY_ID_NAME);

        $this->addForeignKey($this->fkCompanyIdName, $this->tableName, self::FIELD_COMPANY_ID_NAME, 'companies', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkCompanyIdName, $this->tableName);

        $this->dropIndex(self::FIELD_COMPANY_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
