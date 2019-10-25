<?php

use yii\db\Migration;

/**
 * Создается таблица "Отчеты к договорам сопровождения".
 */
class m190911_075136_create_eco_mc_tp_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'eco_mc_tp';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_MC_ID_NAME = 'mc_id';
    const FIELD_REPORT_ID_NAME = 'report_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_MC_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_MC_ID_NAME;
    const FK_REPORT_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_REPORT_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Отчеты к договорам сопровождения"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            self::FIELD_MC_ID_NAME => $this->integer()->notNull()->comment('Договор сопровождения'),
            self::FIELD_REPORT_ID_NAME => $this->integer()->notNull()->comment('Отчет'),
            'date_deadline' => $this->date()->comment('Крайний срок сдачи'),
            'date_fact' => $this->date()->comment('Фактический срок сдачи'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_MC_ID_NAME, self::TABLE_NAME, self::FIELD_MC_ID_NAME);
        $this->createIndex(self::FIELD_REPORT_ID_NAME, self::TABLE_NAME, self::FIELD_REPORT_ID_NAME);

        $this->addForeignKey(self::FK_MC_ID_NAME, self::TABLE_NAME, self::FIELD_MC_ID_NAME, 'eco_mc', 'id');
        $this->addForeignKey(self::FK_REPORT_ID_NAME, self::TABLE_NAME, self::FIELD_REPORT_ID_NAME, 'eco_reports_kinds', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_REPORT_ID_NAME, self::TABLE_NAME);
        $this->dropForeignKey(self::FK_MC_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_REPORT_ID_NAME, self::TABLE_NAME);
        $this->dropIndex(self::FIELD_MC_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
