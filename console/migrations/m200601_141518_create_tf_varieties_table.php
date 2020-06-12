<?php

use yii\db\Migration;

/**
 * Создается таблица "Разновидности форм для участия в тендерах".
 */
class m200601_141518_create_tf_varieties_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'tf_varieties';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Разновидности форм для участия в тендерах"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->batchInsert(self::TABLE_NAME, ['id', 'name'], [
            [
                'id' => 1,
                'name' => 'Газпром',
            ],
            [
                'id' => 2,
                'name' => 'Росатом',
            ],
            [
                'id' => 3,
                'name' => 'Роснефть',
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
