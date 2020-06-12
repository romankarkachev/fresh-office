<?php

use yii\db\Migration;

/**
 * Создается таблица "Взаиморасчеты с подотчетными лицами".
 */
class m191204_135430_create_finance_transactions_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'finance_transactions';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';
    const FIELD_USER_ID_NAME = 'user_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_CREATED_BY_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_CREATED_BY_NAME;
    const FK_USER_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_USER_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Взаиморасчеты с подотчетными лицами"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->notNull()->comment('Автор создания'),
            self::FIELD_USER_ID_NAME => $this->integer()->notNull()->comment('Пользователь системы-подотчетник'),
            'operation' => 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Тип операции (1 - выдача подотчет, 2 - возврат подотчета, 3 - авансовый отчет)"',
            'amount' => $this->decimal(12,2)->notNull()->comment('Сумма'),
            'src_id' => 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Источник средств (1 - наличные, 2 - карта, 3 - расчетный счет)"',
            'comment' => $this->text()->comment('Комментарий'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_USER_ID_NAME, self::TABLE_NAME, self::FIELD_USER_ID_NAME);

        $this->addForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey(self::FK_USER_ID_NAME, self::TABLE_NAME, self::FIELD_USER_ID_NAME, 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_USER_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_USER_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
