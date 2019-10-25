<?php

use yii\db\Migration;

/**
 * Создается таблица "Статусы заявок на документы".
 */
class m181016_141129_create_edf_states_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'edf_states';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Статусы заявок на документы"';
        };

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->batchInsert(self::TABLE_NAME, ['id', 'name'], [
            [
                'id' => 1,
                'name' => 'Черновик',
            ],
            [
                'id' => 2,
                'name' => 'Заявка',
            ],
            [
                'id' => 3,
                'name' => 'Согласование',
            ],
            [
                'id' => 4,
                'name' => 'На подписи у руководства',
            ],
            [
                'id' => 5,
                'name' => 'Подписан руководством',
            ],
            [
                'id' => 6,
                'name' => 'На подписи у заказчика',
            ],
            [
                'id' => 7,
                'name' => 'Отправлен',
            ],
            [
                'id' => 8,
                'name' => 'Доставлен',
            ],
            [
                'id' => 9,
                'name' => 'Завершено',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
