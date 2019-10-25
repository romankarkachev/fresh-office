<?php

use yii\db\Migration;

/**
 * Добавляется поле "Тип документа из Fresh Office".
 */
class m190614_081105_adding_fo_dt_id_to_organizations extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'organizations';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'fo_dt_id', 'TINYINT(1) COMMENT"Тип документа из Fresh Office"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'fo_dt_id');
    }
}
