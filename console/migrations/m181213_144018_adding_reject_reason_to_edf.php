<?php

use yii\db\Migration;

/**
 * Добавляется поле "Причина отказа" в электронный документ.
 */
class m181213_144018_adding_reject_reason_to_edf extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('edf', 'reject_reason', $this->text()->comment('Причина отказа'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('edf', 'reject_reason');
    }
}
