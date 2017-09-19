<?php

use yii\db\Migration;

/**
 * Добавляются поля, определяющие кем и когда добавлен и изменен водитель.
 */
class m170915_211424_adding_created_updated_to_drivers extends Migration
{
    public function up()
    {
        $this->addColumn('drivers', 'created_at', $this->integer()->comment('Дата и время создания') . ' AFTER `id`');
        $this->addColumn('drivers', 'created_by', $this->integer()->comment('Автор создания') . ' AFTER `created_at`');
        $this->addColumn('drivers', 'updated_at', $this->integer()->comment('Дата и время изменения') . ' AFTER `created_by`');
        $this->addColumn('drivers', 'updated_by', $this->integer()->comment('Автор изменений') . ' AFTER `updated_at`');

        $this->createIndex('created_by', 'drivers', 'created_by');
        $this->createIndex('updated_by', 'drivers', 'updated_by');

        $this->addForeignKey('fk_drivers_created_by', 'drivers', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_drivers_updated_by', 'drivers', 'updated_by', 'user', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_drivers_updated_by', 'drivers');
        $this->dropForeignKey('fk_drivers_created_by', 'drivers');

        $this->dropIndex('updated_by', 'drivers');
        $this->dropIndex('created_by', 'drivers');

        $this->dropColumn('drivers', 'updated_by');
        $this->dropColumn('drivers', 'updated_at');
        $this->dropColumn('drivers', 'created_by');
        $this->dropColumn('drivers', 'created_at');
    }
}
