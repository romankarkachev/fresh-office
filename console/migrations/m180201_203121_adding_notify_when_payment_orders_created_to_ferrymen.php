<?php

use yii\db\Migration;

/**
 * Добавляется поле "Необходимость отправлять уведомление перевозчику при импорте платежного ордера на него".
 */
class m180201_203121_adding_notify_when_payment_orders_created_to_ferrymen extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen', 'notify_when_payment_orders_created', 'TINYINT(1) COMMENT"Необходимость отправлять уведомление перевозчику при импорте платежного ордера на него"');
    }

    public function down()
    {
        $this->dropColumn('ferrymen', 'notify_when_payment_orders_created');
    }
}
