<?php

use yii\db\Migration;

/**
 * Создается таблица "Входящая корреспонденция".
 */
class m191211_112137_create_incoming_mail_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'incoming_mail';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';
    const FIELD_TYPE_ID_NAME = 'type_id';
    const FIELD_ORG_ID_NAME = 'org_id';
    const FIELD_RECEIVER_ID_NAME = 'receiver_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_CREATED_BY_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_CREATED_BY_NAME;
    const FK_TYPE_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_TYPE_ID_NAME;
    const FK_ORG_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_ORG_ID_NAME;
    const FK_RECEIVER_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_RECEIVER_ID_NAME;

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
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->notNull()->comment('Автор создания'),
            'inc_num' => $this->string(30)->notNull()->comment('Входящий номер'),
            self::FIELD_TYPE_ID_NAME => $this->integer()->notNull()->comment('Тип'),
            self::FIELD_ORG_ID_NAME => $this->integer()->comment('Получатель письма (организация)'),
            'description' => $this->text()->comment('Опись вложения'),
            'date_complete_before' => $this->date()->comment('Срок исполнения'),
            'ca_src' => 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Источник данных для поля Отправитель"',
            'ca_id' => $this->integer()->comment('Идентификатор контрагента-отправителя'),
            'ca_name' => $this->string()->comment('Наименование контрагента-отправителя'),
            self::FIELD_RECEIVER_ID_NAME => $this->integer()->comment('Получатель письма (физлицо)'),
            'comment' => $this->text()->comment('Комментарий'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_TYPE_ID_NAME, self::TABLE_NAME, self::FIELD_TYPE_ID_NAME);
        $this->createIndex(self::FIELD_ORG_ID_NAME, self::TABLE_NAME, self::FIELD_ORG_ID_NAME);
        $this->createIndex(self::FIELD_RECEIVER_ID_NAME, self::TABLE_NAME, self::FIELD_RECEIVER_ID_NAME);

        $this->addForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey(self::FK_TYPE_ID_NAME, self::TABLE_NAME, self::FIELD_TYPE_ID_NAME, 'incoming_mail_types', 'id');
        $this->addForeignKey(self::FK_ORG_ID_NAME, self::TABLE_NAME, self::FIELD_ORG_ID_NAME, 'organizations', 'id');
        $this->addForeignKey(self::FK_RECEIVER_ID_NAME, self::TABLE_NAME, self::FIELD_RECEIVER_ID_NAME, 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_RECEIVER_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_ORG_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_TYPE_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_RECEIVER_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_ORG_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_TYPE_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
