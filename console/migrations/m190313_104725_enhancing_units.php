<?php

use yii\db\Migration;

/**
 * В таблицу единиц измерения добавляется столбец, в котором будет храниться их международные коды.
 */
class m190313_104725_enhancing_units extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('units', 'name_full', $this->string(100)->comment('Наименование полное'));
        $this->addColumn('units', 'code', $this->string(3)->comment('Международный код'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('units', 'code');
        $this->dropColumn('units', 'name_full');
    }
}
