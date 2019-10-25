<?php

use yii\db\Migration;

/**
 * Создается таблица "Статусы тендеров".
 */
class m190916_145407_create_tenders_states_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'tenders_states';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Статусы тендеров"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->batchInsert(self::TABLE_NAME, ['id', 'name'], [
            [
                'id' => 1,
                'name' => 'Новый',
            ],
            [
                'id' => 2,
                'name' => 'Согласован',
            ],
            [
                'id' => 3,
                'name' => 'Отказ',
            ],
            [
                'id' => 4,
                'name' => 'В работе',
            ],
            [
                'id' => 5,
                'name' => 'Заявка подана',
            ],
            [
                'id' => 6,
                'name' => 'Проигрыш',
            ],
            [
                'id' => 7,
                'name' => 'Отмена заказчиком',
            ],
            [
                'id' => 8,
                'name' => 'Без результатов',
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
