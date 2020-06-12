<?php

use yii\db\Migration;

/**
 * Создается таблица "Возможные причины проигрышей по тендерам".
 */
class m200519_144558_create_tenders_loss_reasons_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'tenders_loss_reasons';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Возможные причины проигрышей по тендерам"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->batchInsert(self::TABLE_NAME, ['id', 'name'], [
            [
                'id' => 1,
                'name' => 'Цена',
            ],
            [
                'id' => 2,
                'name' => 'Ошибка',
            ],
            [
                'id' => 3,
                'name' => 'Другое',
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
