<?php

use yii\db\Migration;

/**
 * Создается таблица "Договоры сопровождения по экологии".
 */
class m190911_075124_create_eco_mc_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'eco_mc';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';
    const FIELD_MANAGER_ID_NAME = 'manager_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_CREATED_BY_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_CREATED_BY_NAME;
    const FK_MANAGER_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_MANAGER_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Договоры сопровождения по экологии"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->notNull()->comment('Автор создания'),
            'fo_ca_id' => $this->integer()->notNull()->comment('Контрагент из Fresh Office'),
            self::FIELD_MANAGER_ID_NAME => $this->integer()->notNull()->comment('Ответственный по договору'),
            'amount' => $this->decimal(12,2)->comment('Сумма'),
            'date_start' => $this->date()->comment('Дата начала действия договора'),
            'date_finish' => $this->date()->comment('Дата завершения действия договора'),
            'comment' => $this->text()->comment('Комментарий'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_MANAGER_ID_NAME, self::TABLE_NAME, self::FIELD_MANAGER_ID_NAME);

        $this->addForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey(self::FK_MANAGER_ID_NAME, self::TABLE_NAME, self::FIELD_MANAGER_ID_NAME, 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_MANAGER_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_MANAGER_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
