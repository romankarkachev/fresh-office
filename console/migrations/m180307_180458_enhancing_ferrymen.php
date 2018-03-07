<?php

use yii\db\Migration;

/**
 * Добавляется ряд полей с идентификацией перевозчика.
 */
class m180307_180458_enhancing_ferrymen extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen', 'name_full', $this->text()->comment('Полное наименование') . ' AFTER `name_crm`');
        $this->addColumn('ferrymen', 'name_short', $this->text()->comment('Сокращенное наименование наименование') . ' AFTER `name_full`');
        $this->addColumn('ferrymen', 'inn', $this->string(12)->comment('ИНН') . ' AFTER `name_short`');
        $this->addColumn('ferrymen', 'kpp', $this->string(9)->comment('КПП') . ' AFTER `inn`');
        $this->addColumn('ferrymen', 'ogrn', $this->string(15)->comment('ОГРН(ИП)') . ' AFTER `kpp`');
        $this->addColumn('ferrymen', 'address_j', $this->text()->comment('Адрес юридический') . ' AFTER `ogrn`');
        $this->addColumn('ferrymen', 'address_f', $this->text()->comment('Адрес фактический') . ' AFTER `address_j`');
    }

    public function down()
    {
        $this->dropColumn('ferrymen', 'name_full');
        $this->dropColumn('ferrymen', 'name_short');
        $this->dropColumn('ferrymen', 'inn');
        $this->dropColumn('ferrymen', 'kpp');
        $this->dropColumn('ferrymen', 'ogrn');
        $this->dropColumn('ferrymen', 'address_j');
        $this->dropColumn('ferrymen', 'address_f');
    }
}
