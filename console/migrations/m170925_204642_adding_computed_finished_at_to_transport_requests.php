<?php

use yii\db\Migration;

/**
 * Добавляется поле "Дата и время закрытия заявки без учета выходных".
 */
class m170925_204642_adding_computed_finished_at_to_transport_requests extends Migration
{
    public function up()
    {
        $this->addColumn('transport_requests', 'computed_finished_at', $this->integer()->comment('Дата и время закрытия заявки без учета выходных') . ' AFTER `finished_at`');
    }

    public function down()
    {
        $this->dropColumn('transport_requests', 'computed_finished_at');
    }

}
