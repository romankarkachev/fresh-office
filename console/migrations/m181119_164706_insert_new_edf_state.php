<?php

use yii\db\Migration;

/**
 * Вставляется новый статус для электронных документов.
 */
class m181119_164706_insert_new_edf_state extends Migration
{
    /**
     * @var string наименование таблицы, в которую добавляется запись
     */
    private $tableName;

    /**
     * @inheritdoc
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
        $this->insert($this->tableName, [
            'id' => 10,
            'name' => 'Отказ',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete($this->tableName, [
            'id' => 10,
        ]);
    }
}
