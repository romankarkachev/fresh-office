<?php

use yii\db\Migration;

/**
 * Создается таблица "Причины проигрышей тендеров".
 */
class m200519_145058_create_tenders_lr_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'tenders_lr';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_TENDER_ID_NAME = 'tender_id';
    const FIELD_LR_ID_NAME = 'lr_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_TENDER_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_TENDER_ID_NAME;
    const FK_LR_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_LR_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Причины проигрышей тендеров"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            self::FIELD_TENDER_ID_NAME => $this->integer()->notNull()->comment('Тендер'),
            self::FIELD_LR_ID_NAME => $this->integer()->notNull()->comment('Причина проигрыша'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_TENDER_ID_NAME, self::TABLE_NAME, self::FIELD_TENDER_ID_NAME);
        $this->createIndex(self::FIELD_LR_ID_NAME, self::TABLE_NAME, self::FIELD_LR_ID_NAME);

        $this->addForeignKey(self::FK_TENDER_ID_NAME, self::TABLE_NAME, self::FIELD_TENDER_ID_NAME, 'tenders', 'id');
        $this->addForeignKey(self::FK_LR_ID_NAME, self::TABLE_NAME, self::FIELD_LR_ID_NAME, 'tenders_loss_reasons', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_LR_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_TENDER_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_LR_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_TENDER_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
