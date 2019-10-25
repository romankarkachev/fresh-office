<?php

use yii\db\Migration;

/**
 * В таблицу наших организаций добавляются поля "Адрес для ТТН", "Шаблон номеров договоров" и сотни тысяч других.
 */
class m181016_125839_enhancing_organizations extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'organizations';

        parent::init();
    }

    public function up()
    {
        $this->addColumn($this->tableName, 'address_ttn', $this->text()->comment('Адрес для ТТН') . ' AFTER `address_f`');

        $this->addColumn($this->tableName, 'doc_num_tmpl', $this->string(30)->comment('Шаблон номера договора') . ' AFTER `address_ttn`');

        $this->addColumn($this->tableName, 'dir_post', $this->string()->comment('Должность директора для реквизитов'));

        $this->addColumn($this->tableName, 'dir_name', $this->string()->comment('ФИО директора полностью'));

        $this->addColumn($this->tableName, 'dir_name_short', $this->string()->comment('Сокращенные ФИО директора'));

        $this->addColumn($this->tableName, 'dir_name_of', $this->string()->comment('ФИО директора в родительном падеже'));

        $this->addColumn($this->tableName, 'phones', $this->string()->comment('Телефоны для реквизитов'));

        $this->addColumn($this->tableName, 'email', $this->string()->comment('Email для реквизитов'));
    }

    public function down()
    {
        $this->dropColumn($this->tableName, 'email');

        $this->dropColumn($this->tableName, 'phones');

        $this->dropColumn($this->tableName, 'dir_name_of');

        $this->dropColumn($this->tableName, 'dir_name_short');

        $this->dropColumn($this->tableName, 'dir_name');

        $this->dropColumn($this->tableName, 'dir_post');

        $this->dropColumn($this->tableName, 'doc_num_tmpl');

        $this->dropColumn($this->tableName, 'address_ttn');
    }
}
