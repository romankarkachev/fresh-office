<?php

use yii\db\Migration;

/**
 * Добавляются поля, определяющие кем и когда добавлен и изменен автомобиль.
 */
class m170915_211431_adding_created_updated_to_transport extends Migration
{
    public function up()
    {
        $this->addColumn('transport', 'created_at', $this->integer()->comment('Дата и время создания') . ' AFTER `id`');
        $this->addColumn('transport', 'created_by', $this->integer()->comment('Автор создания') . ' AFTER `created_at`');
        $this->addColumn('transport', 'updated_at', $this->integer()->comment('Дата и время изменения') . ' AFTER `created_by`');
        $this->addColumn('transport', 'updated_by', $this->integer()->comment('Автор изменений') . ' AFTER `updated_at`');

        $this->createIndex('created_by', 'transport', 'created_by');
        $this->createIndex('updated_by', 'transport', 'updated_by');

        $this->addForeignKey('fk_transport_created_by', 'transport', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_transport_updated_by', 'transport', 'updated_by', 'user', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_transport_updated_by', 'transport');
        $this->dropForeignKey('fk_transport_created_by', 'transport');

        $this->dropIndex('updated_by', 'transport');
        $this->dropIndex('created_by', 'transport');

        $this->dropColumn('transport', 'updated_by');
        $this->dropColumn('transport', 'updated_at');
        $this->dropColumn('transport', 'created_by');
        $this->dropColumn('transport', 'created_at');
    }
}
