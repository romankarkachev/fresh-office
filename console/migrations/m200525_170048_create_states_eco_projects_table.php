<?php

use yii\db\Migration;

/**
 * Добавляется таблица "Статусы проектов по экологии".
 */
class m200525_170048_create_states_eco_projects_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'states_eco_projects';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Статусы проектов по экологии"';
        }

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
                'name' => 'Ожидание Исполнителя',
            ],
            [
                'id' => 3,
                'name' => 'Ожидание Заказчика',
            ],
            [
                'id' => 4,
                'name' => 'Надзорный орган',
            ],
            [
                'id' => 5,
                'name' => 'Прочее',
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
