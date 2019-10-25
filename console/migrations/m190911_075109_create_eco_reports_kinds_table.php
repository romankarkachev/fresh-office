<?php

use yii\db\Migration;

/**
 * Создается таблица "Разновидности отчетов по экологии в контролирующие органы".
 */
class m190911_075109_create_eco_reports_kinds_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'eco_reports_kinds';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Разновидности отчетов по экологии в контролирующие органы"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->comment('Наименование'),
            'gov_agency' => $this->string()->comment('Наименование органа, принимающего отчет'),
            'periodicity' => 'TINYINT(1) COMMENT"Периодичность подачи отчета"',
            'sort' => $this->integer()->comment('Номер по порядку'),
        ], $tableOptions);

        $this->batchInsert(self::TABLE_NAME, ['id', 'name', 'sort'], [
            [
                'id' => 1,
                'name' => '4-ОС',
                'sort' => 1,
            ],
            [
                'id' => 2,
                'name' => '2-ТП (отходы)',
                'sort' => 2,
            ],
            [
                'id' => 3,
                'name' => 'Кадастр отходов',
                'sort' => 3,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
