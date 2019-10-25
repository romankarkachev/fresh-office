<?php

use yii\db\Migration;

/**
 * Создается таблица "Разновидности конкурсов".
 */
class m191017_084927_create_tenders_kinds_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'tenders_kinds';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Разновидности конкурсов"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->comment('Наименование'),
            'keywords' => $this->string()->comment('Ключевые слова для идентификации'),
        ], $tableOptions);

        $this->batchInsert(self::TABLE_NAME, ['id', 'name', 'keywords'], [
            [
                'id' => 1,
                'name' => 'Аукцион',
                'keywords' => 'Электронный аукцион',
            ],
            [
                'id' => 2,
                'name' => 'Открытый конкурс',
                'keywords' => 'Открытый конкурс',
            ],
            [
                'id' => 3,
                'name' => 'Запрос котировок',
                'keywords' => 'Запрос котировок в электронной форме',
            ],
            [
                'id' => 4,
                'name' => 'Запрос предложений',
                'keywords' => 'Открытый запрос предложений',
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
