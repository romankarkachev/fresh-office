<?php

use yii\db\Migration;

/**
 * Добавляется новый статус электронного документа "Отказ клиента".
 */
class m190212_105836_adding_edf_states extends Migration
{
    /**
     * @var string наименование таблицы, в которую добавляется запись
     */
    private $tableName;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->tableName = 'edf_states';

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert($this->tableName, ['id', 'name'], [
            [
                'id' => 11,
                'name' => 'Отказ клиента',
            ],
            [
                'id' => 12,
                'name' => 'Отдать на подпись',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete($this->tableName, [
            'id' => [11, 12],
        ]);
    }
}
