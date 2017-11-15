<?php

use yii\db\Migration;

/**
 * Добавляется поле "Организация".
 */
class m171031_125314_adding_org_id_to_licenses_requests extends Migration
{
    public function up()
    {
        $this->addColumn('licenses_requests', 'org_id', $this->integer()->comment('Организация, чьи сканы должны быть отправлены') . ' AFTER `ca_id`');

        $this->createIndex('org_id', 'licenses_requests', 'org_id');

        $this->addForeignKey('fk_licenses_requests_org_id', 'licenses_requests', 'org_id', 'organizations', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_licenses_requests_org_id', 'licenses_requests');

        $this->dropIndex('org_id', 'licenses_requests');

        $this->dropColumn('licenses_requests', 'org_id');
    }
}
