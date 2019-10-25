<?php

use yii\db\Migration;

/**
 * Создается таблица с доступом пользователей к статьям расходов (для платежных ордеров по бюджету).
 */
class m190614_082205_create_users_ei_access_table extends Migration
{
    /**
     * @var string наименование таблицы, которая создается
     */
    const TABLE_NAME = 'users_ei_access';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_USER_ID_NAME = 'user_id';
    const FIELD_EI_ID_NAME = 'ei_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_USER_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_USER_ID_NAME;
    const FK_EI_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_EI_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Доступ пользователей к статьям расходов"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            self::FIELD_USER_ID_NAME => $this->integer()->notNull()->comment('Пользователь'),
            self::FIELD_EI_ID_NAME => $this->integer()->notNull()->comment('Статья расходов'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_USER_ID_NAME, self::TABLE_NAME, self::FIELD_USER_ID_NAME);
        $this->createIndex(self::FIELD_EI_ID_NAME, self::TABLE_NAME, self::FIELD_EI_ID_NAME);

        $this->addForeignKey(self::FK_USER_ID_NAME, self::TABLE_NAME, self::FIELD_USER_ID_NAME, 'user', 'id');
        $this->addForeignKey(self::FK_EI_ID_NAME, self::TABLE_NAME, self::FIELD_EI_ID_NAME, 'po_ei', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_EI_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_USER_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_EI_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_USER_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
