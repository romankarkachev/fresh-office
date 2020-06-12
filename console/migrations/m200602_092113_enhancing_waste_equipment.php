<?php

use yii\db\Migration;

/**
 * .
 */
class m200602_092113_enhancing_waste_equipment extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'waste_equipment';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'description', $this->text()->comment('Производитель, страна производства, марка, модель, основные технические характеристики'));
        $this->addColumn(self::TABLE_NAME, 'year', $this->smallInteger()->comment('Год выпуска'));
        $this->addColumn(self::TABLE_NAME, 'amort_percent', 'TINYINT(1) COMMENT"% амортизации"');
        $this->addColumn(self::TABLE_NAME, 'ownership', 'TINYINT(1) COMMENT"Принадлежность (1 - собственность, 2 - арендованный)"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'ownership');
        $this->dropColumn(self::TABLE_NAME, 'amort_percent');
        $this->dropColumn(self::TABLE_NAME, 'year');
        $this->dropColumn(self::TABLE_NAME, 'description');
    }
}
