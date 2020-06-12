<?php

use yii\db\Migration;

/**
 * Создается таблица "Подотчетные лица".
 */
class m191204_134922_create_finance_advance_holders_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'finance_advance_holders';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_USER_ID_NAME = 'user_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_USER_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_USER_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Подотчетные лица"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('Пользователь системы-подотчетник'),
            'balance' => $this->decimal(12,2)->notNull()->comment('Баланс (остаток, состояние взаиморасчетов)'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_USER_ID_NAME, self::TABLE_NAME, self::FIELD_USER_ID_NAME);

        $this->addForeignKey(self::FK_USER_ID_NAME, self::TABLE_NAME, self::FIELD_USER_ID_NAME, 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_USER_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_USER_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
