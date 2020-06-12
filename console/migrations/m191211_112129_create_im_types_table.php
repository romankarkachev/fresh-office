<?php

use yii\db\Migration;

/**
 * Создается таблица "Разновидности входящей корреспонденции".
 */
class m191211_112129_create_im_types_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'incoming_mail_types';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Разновидности входящей корреспонденции"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->batchInsert(self::TABLE_NAME, ['id', 'name'], [
            [
                'id' => 1,
                'name' => 'Письмо',
            ],
            [
                'id' => 2,
                'name' => 'Письмо-запрос',
            ],
            [
                'id' => 3,
                'name' => 'Жалоба',
            ],
            [
                'id' => 4,
                'name' => 'Требование',
            ],
            [
                'id' => 5,
                'name' => 'Претензия',
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
