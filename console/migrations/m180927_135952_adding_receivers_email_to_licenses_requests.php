<?php

use yii\db\Migration;

/**
 * Добавляется поле для ввода E-mail, на который придут сканы лицензий в случае одобрения.
 */
class m180927_135952_adding_receivers_email_to_licenses_requests extends Migration
{
    public function up()
    {
        $this->addColumn('licenses_requests', 'receivers_email', $this->string()->notNull()->comment('E-mail получателя сканов лицензий в случае одобрения') . ' AFTER `org_id`');
    }

    public function down()
    {
        $this->dropColumn('licenses_requests', 'receivers_email');
    }
}
