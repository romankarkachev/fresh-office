<?php

use yii\db\Migration;

/**
 * Поле "Автор загрузки" в файлах к тендерам делается необязательным.
 */
class m191120_083139_alter_tenders_files_uploaded_by extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'tenders_files';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_UPLOADED_BY_NAME = 'uploaded_by';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(self::TABLE_NAME, self::FIELD_UPLOADED_BY_NAME, $this->integer()->comment('Автор загрузки'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(self::TABLE_NAME, self::FIELD_UPLOADED_BY_NAME, $this->integer()->notNull()->comment('Автор загрузки'));
    }
}
