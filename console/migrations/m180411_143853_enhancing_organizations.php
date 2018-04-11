<?php

use yii\db\Migration;

/**
 * Добавляются поля с реквизитами организаций.
 */
class m180411_143853_enhancing_organizations extends Migration
{
    public function up()
    {
        $this->addColumn('organizations', 'inn', $this->string(12)->comment('ИНН') . ' AFTER `name_full`');
        $this->addColumn('organizations', 'kpp', $this->string(9)->comment('КПП') . ' AFTER `inn`');
        $this->addColumn('organizations', 'ogrn', $this->string(15)->comment('ОГРН(ИП)') . ' AFTER `kpp`');
        $this->addColumn('organizations', 'address_j', $this->text()->comment('Адрес юридический') . ' AFTER `ogrn`');
        $this->addColumn('organizations', 'address_f', $this->text()->comment('Адрес фактический') . ' AFTER `address_j`');
    }

    public function down()
    {
        $this->dropColumn('organizations', 'inn');
        $this->dropColumn('organizations', 'kpp');
        $this->dropColumn('organizations', 'ogrn');
        $this->dropColumn('organizations', 'address_j');
        $this->dropColumn('organizations', 'address_f');
    }
}
