<?php

use yii\db\Migration;

/**
 * Добавляются поля "Номер редакции" и "ID в источнике" в таблицу файлов к тендерам.
 */
class m190920_122334_enhancing_tenders_files extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'tenders_files';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CT_ID_NAME = 'ct_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_CT_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_CT_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'revision', $this->string(3)->comment('Редакция заявки'));
        $this->addColumn(self::TABLE_NAME, 'src_id', $this->integer()->comment('Идентификатор в источнике'));

        $this->addColumn(self::TABLE_NAME, self::FIELD_CT_ID_NAME, $this->integer()->comment('Тип контента'));
        $this->createIndex(self::FIELD_CT_ID_NAME, self::TABLE_NAME, self::FIELD_CT_ID_NAME);
        $this->addForeignKey(self::FK_CT_ID_NAME, self::TABLE_NAME, self::FIELD_CT_ID_NAME, 'tenders_content_types', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_CT_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_CT_ID_NAME, self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::FIELD_CT_ID_NAME);

        $this->dropColumn(self::TABLE_NAME, 'src_id');
        $this->dropColumn(self::TABLE_NAME, 'revision');
    }
}
