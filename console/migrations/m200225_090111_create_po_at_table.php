<?php

use yii\db\Migration;

/**
 * Создается таблица "Шаблоны бюджетных автоплатежей".
 */
class m200225_090111_create_po_at_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'po_at';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_COMPANY_ID_NAME = 'company_id';
    const FIELD_EI_ID_NAME = 'ei_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_COMPANY_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_COMPANY_ID_NAME;
    const FK_EI_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_EI_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Шаблоны бюджетных автоплатежей"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'is_active' => 'TINYINT(1) NOT NULL DEFAULT "1" COMMENT"Признак активности"',
            self::FIELD_COMPANY_ID_NAME => $this->integer()->notNull()->comment('Контрагент'),
            self::FIELD_EI_ID_NAME => $this->integer()->notNull()->comment('Статья расходов'),
            'amount' => $this->decimal(12,2)->comment('Сумма'),
            'comment' => $this->text()->comment('Комментарий'),
            'properties' => $this->text()->comment('Свойства'),
            'periodicity' => 'TINYINT(1) NOT NULL DEFAULT "1" COMMENT"Число месяца"',
        ], $tableOptions);

        $this->createIndex(self::FIELD_COMPANY_ID_NAME, self::TABLE_NAME, self::FIELD_COMPANY_ID_NAME);
        $this->createIndex(self::FIELD_EI_ID_NAME, self::TABLE_NAME, self::FIELD_EI_ID_NAME);

        $this->addForeignKey(self::FK_COMPANY_ID_NAME, self::TABLE_NAME, self::FIELD_COMPANY_ID_NAME, \common\models\Companies::tableName(), 'id');
        $this->addForeignKey(self::FK_EI_ID_NAME, self::TABLE_NAME, self::FIELD_EI_ID_NAME, \common\models\PoEi::tableName(), 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_EI_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_COMPANY_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_EI_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_COMPANY_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
