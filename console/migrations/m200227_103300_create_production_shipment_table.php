<?php

use yii\db\Migration;

/**
 * Создается таблица для хранения отправок техники с производственных площадок.
 */
class m200227_103300_create_production_shipment_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'production_shipment';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';
    const FIELD_TRANSPORT_ID_NAME = 'transport_id';
    const FIELD_SITE_ID_NAME = 'site_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_CREATED_BY_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_CREATED_BY_NAME;
    const FK_TRANSPORT_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_TRANSPORT_ID_NAME;
    const FK_SITE_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_SITE_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Отправки техники с производственных площадок"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->notNull()->comment('Автор создания'),
            'rn' => $this->string(30)->notNull()->comment('Госномер'),
            self::FIELD_TRANSPORT_ID_NAME => $this->integer()->comment('Транспортное средство'),
            self::FIELD_SITE_ID_NAME => $this->integer()->comment('Производственная площадка'),
            'fo_project_id' => $this->integer()->comment('Проект из CRM Fresh Office'),
            'subject' => $this->string()->comment('Заголовок письма'),
            'comment' => $this->text()->comment('Комментарий'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_TRANSPORT_ID_NAME, self::TABLE_NAME, self::FIELD_TRANSPORT_ID_NAME);
        $this->createIndex(self::FIELD_SITE_ID_NAME, self::TABLE_NAME, self::FIELD_SITE_ID_NAME);

        $this->addForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey(self::FK_TRANSPORT_ID_NAME, self::TABLE_NAME, self::FIELD_TRANSPORT_ID_NAME, \common\models\Transport::tableName(), 'id');
        $this->addForeignKey(self::FK_SITE_ID_NAME, self::TABLE_NAME, self::FIELD_SITE_ID_NAME, 'production_sites', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_SITE_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_TRANSPORT_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_SITE_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_TRANSPORT_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
