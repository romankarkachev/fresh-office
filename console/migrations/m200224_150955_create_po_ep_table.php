<?php

use yii\db\Migration;

/**
 * Добавляется таблица "Привязка проектов по экологии к платежным ордерам по бюджету".
 */
class m200224_150955_create_po_ep_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'po_ep';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_PO_ID_NAME = 'po_id';
    const FIELD_EP_ID_NAME = 'ep_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_PO_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_PO_ID_NAME;
    const FK_EP_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_EP_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Привязка проектов по экологии к платежным ордерам по бюджету"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            self::FIELD_PO_ID_NAME => $this->integer()->notNull()->comment('Платежный ордер'),
            self::FIELD_EP_ID_NAME => $this->integer()->notNull()->comment('Проект по экологии'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_PO_ID_NAME, self::TABLE_NAME, self::FIELD_PO_ID_NAME);
        $this->createIndex(self::FIELD_EP_ID_NAME, self::TABLE_NAME, self::FIELD_EP_ID_NAME);

        $this->addForeignKey(self::FK_PO_ID_NAME, self::TABLE_NAME, self::FIELD_PO_ID_NAME, \common\models\Po::tableName(), 'id');
        $this->addForeignKey(self::FK_EP_ID_NAME, self::TABLE_NAME, self::FIELD_EP_ID_NAME, \common\models\EcoProjects::tableName(), 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_EP_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_PO_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_EP_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_PO_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
