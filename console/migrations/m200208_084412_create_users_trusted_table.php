<?php

use yii\db\Migration;

/**
 * Создается таблица "Доверенные лица пользователей".
 */
class m200208_084412_create_users_trusted_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'users_trusted';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_USER_ID_NAME = 'user_id';
    const FIELD_TRUSTED_ID_NAME = 'trusted_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_USER_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_USER_ID_NAME;
    const FK_TRUSTED_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_TRUSTED_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Входящая корреспонденция"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            self::FIELD_USER_ID_NAME => $this->integer()->notNull()->comment('Пользователь'),
            'section' => 'TINYINT NOT NULL COMMENT"Раздел учета"',
            self::FIELD_TRUSTED_ID_NAME => $this->integer()->notNull()->comment('Доверенное лицо'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_USER_ID_NAME, self::TABLE_NAME, self::FIELD_USER_ID_NAME);
        $this->createIndex(self::FIELD_TRUSTED_ID_NAME, self::TABLE_NAME, self::FIELD_TRUSTED_ID_NAME);

        $this->addForeignKey(self::FK_USER_ID_NAME, self::TABLE_NAME, self::FIELD_USER_ID_NAME, 'user', 'id');
        $this->addForeignKey(self::FK_TRUSTED_ID_NAME, self::TABLE_NAME, self::FIELD_TRUSTED_ID_NAME, 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_TRUSTED_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_USER_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_TRUSTED_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_USER_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
