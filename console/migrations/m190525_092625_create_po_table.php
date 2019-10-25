<?php

use yii\db\Migration;

/**
 * Создается таблица "Платежные ордеры (бюджет)".
 */
class m190525_092625_create_po_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';
    const FIELD_STATE_ID_NAME = 'state_id';
    const FIELD_COMPANY_ID_NAME = 'company_id';
    const FIELD_EI_ID_NAME = 'ei_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля created_by
     */
    private $fkCreatedByName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля state_id
     */
    private $fkStateIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля company_id
     */
    private $fkCompanyIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля ei_id
     */
    private $fkEiIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'po';
        $this->fkCreatedByName = 'fk_' . $this->tableName . '_' . self::FIELD_CREATED_BY_NAME;
        $this->fkStateIdName = 'fk_' . $this->tableName . '_' . self::FIELD_STATE_ID_NAME;
        $this->fkCompanyIdName = 'fk_' . $this->tableName . '_' . self::FIELD_COMPANY_ID_NAME;
        $this->fkEiIdName = 'fk_' . $this->tableName . '_' . self::FIELD_EI_ID_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Платежные ордеры (бюджет)"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->notNull()->comment('Автор создания'),
            self::FIELD_STATE_ID_NAME => $this->integer()->notNull()->comment('Текущий статус'),
            self::FIELD_COMPANY_ID_NAME => $this->integer()->notNull()->comment('Контрагент'),
            self::FIELD_EI_ID_NAME => $this->integer()->notNull()->comment('Статья расходов'),
            'amount' => $this->decimal(12,2)->comment('Сумма'),
            'approved_at' => $this->integer()->comment('Дата и время согласования ордера'),
            'paid_at' => $this->integer()->comment('Дата и время оплаты'),
            'comment' => $this->text()->comment('Комментарий'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, $this->tableName, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_STATE_ID_NAME, $this->tableName, self::FIELD_STATE_ID_NAME);
        $this->createIndex(self::FIELD_COMPANY_ID_NAME, $this->tableName, self::FIELD_COMPANY_ID_NAME);
        $this->createIndex(self::FIELD_EI_ID_NAME, $this->tableName, self::FIELD_EI_ID_NAME);

        $this->addForeignKey($this->fkCreatedByName, $this->tableName, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey($this->fkStateIdName, $this->tableName, self::FIELD_STATE_ID_NAME, 'payment_orders_states', 'id');
        $this->addForeignKey($this->fkCompanyIdName, $this->tableName, self::FIELD_COMPANY_ID_NAME, 'companies', 'id');
        $this->addForeignKey($this->fkEiIdName, $this->tableName, self::FIELD_EI_ID_NAME, 'po_ei', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkEiIdName, $this->tableName);
        $this->dropForeignKey($this->fkCompanyIdName, $this->tableName);
        $this->dropForeignKey($this->fkStateIdName, $this->tableName);
        $this->dropForeignKey($this->fkCreatedByName, $this->tableName);

        $this->dropIndex(self::FIELD_EI_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_COMPANY_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_STATE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
