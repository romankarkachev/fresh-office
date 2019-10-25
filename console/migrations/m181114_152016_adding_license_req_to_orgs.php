<?php

use yii\db\Migration;

/**
 * Добавляется поле "Реквизиты лицензии" в таблицу организаций.
 */
class m181114_152016_adding_license_req_to_orgs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'license_req', $this->string(100)->comment('Реквизиты лицензии'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'license_req');
    }
}
