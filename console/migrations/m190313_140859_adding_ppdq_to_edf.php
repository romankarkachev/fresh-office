<?php

use yii\db\Migration;

/**
 * В электронный документооборот добавляется поле "Количество дней постоплаты".
 */
class m190313_140859_adding_ppdq_to_edf extends Migration
{
    const TABLE_NAME = 'edf';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'ppdq', $this->integer()->comment('Количество дней постоплаты'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'ppdq');
    }
}
