<?php

use yii\db\Migration;

/**
 * Добавляются поля "Стоимость" и "Себестоимость" в таблицу проектов (для модуля по подбору перевозчиков).
 */
class m181213_131447_adding_amount_to_projects extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('projects', 'amount', $this->decimal(12,2)->comment('Стоимость'));
        $this->addColumn('projects', 'cost', $this->decimal(12,2)->comment('Себестоимость'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('projects', 'cost');
        $this->dropColumn('projects', 'amount');
    }
}
