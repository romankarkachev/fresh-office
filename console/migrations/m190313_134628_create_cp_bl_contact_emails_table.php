<?php

use yii\db\Migration;

/**
 * Создается таблица, в которой будут содержаться адреса E-mail контактных лиц контрагентов, которые необходимо исключить
 * из рассылки уведомлений о состоянии почтовых отправлений.
 */
class m190313_134628_create_cp_bl_contact_emails_table extends Migration
{
    /**
     * Наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'cp_bl_contact_emails';

    /**
     * Поля, которые имеют индексы
     */
    const FIELD_EMAIL_NAME = 'email';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "E-mail контактных лиц контрагентов, которые необходимо исключить из рассылки уведомлений о состоянии почтовых отправлений"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->comment('Дата и время отписки'),
            'fo_ca_id' => $this->integer()->comment('Контрагент'),
            self::FIELD_EMAIL_NAME => $this->string()->notNull()->comment('Адрес E-mail'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_EMAIL_NAME, self::TABLE_NAME, self::FIELD_EMAIL_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(self::FIELD_EMAIL_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
