<?php

use yii\db\Migration;

/**
 * Создается таблица "Очередь звонков для распознавания".
 */
class m191024_085534_create_yskrq_table extends Migration
{
    /**
     * @var string наименование таблицы, которая создается
     */
    const TABLE_NAME = 'yskrq';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_CREATED_BY_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_CREATED_BY_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Очередь звонков для распознавания (Yandex SpeechKit recognition queue)"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время постановки в очередь'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->comment('Пользователь, который отправил файл на распознавание'),
            'check_after' => $this->integer()->comment('Дата и время, раньше которых распознавание не может быть завершено (проверять не ранее этой даты)'),
            'call_id' => $this->integer()->notNull()->comment('Звонок'),
            'url_bucket' => $this->string()->comment('Ссылка на файл, размещенный в бакете'),
            'operation_id' => $this->string(30)->comment('Идентификатор операции для проверки готовности распознавания'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME);

        $this->addForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME, 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
