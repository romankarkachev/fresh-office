<?php

use yii\db\Migration;

/**
 * Добавляется поле "Контрагенты из проектов" в таблицу платежных ордеров.
 * Добавляется поле "Признак импорта / ввода ордера вручную".
 * Добавляется поле "Дата и время отправки письма перевозчику".
 * Добавляется поле "Дата и время согласования ордера".
 */
class m180202_080935_enhancing_payment_orders extends Migration
{
    public function up()
    {
        $this->addColumn('payment_orders', 'cas', $this->text()->comment('Контрагенты из проектов') . ' AFTER `projects`');
        $this->addColumn('payment_orders', 'creation_type', 'TINYINT(1) DEFAULT"1" COMMENT"Способ создания (1 - создано вручную, 2 - импорт из файла Excel)" AFTER `created_by`');
        $this->addColumn('payment_orders', 'emf_sent_at', $this->integer()->comment('Дата и время отправки письма перевозчику') . ' AFTER `payment_date`');
        $this->addColumn('payment_orders', 'approved_at', $this->integer()->comment('Дата и время согласования ордера') . ' AFTER `emf_sent_at`');
    }

    public function down()
    {
        $this->dropColumn('payment_orders', 'cas');
        $this->dropColumn('payment_orders', 'creation_type');
        $this->dropColumn('payment_orders', 'emf_sent_at');
        $this->dropColumn('payment_orders', 'approved_at');
    }
}
