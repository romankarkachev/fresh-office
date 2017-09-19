<?php

use yii\db\Migration;

/**
 * Добавляются поля, определяющие кем и когда добавлен и изменен перевозчик.
 */
class m170915_205040_adding_created_updated_to_ferrymen extends Migration
{
    public function up()
    {
        $this->addColumn('ferrymen', 'created_at', $this->integer()->comment('Дата и время создания') . ' AFTER `id`');
        $this->addColumn('ferrymen', 'created_by', $this->integer()->comment('Автор создания') . ' AFTER `created_at`');
        $this->addColumn('ferrymen', 'updated_at', $this->integer()->comment('Дата и время изменения') . ' AFTER `created_by`');
        $this->addColumn('ferrymen', 'updated_by', $this->integer()->comment('Автор изменений') . ' AFTER `updated_at`');

        $this->createIndex('created_by', 'ferrymen', 'created_by');
        $this->createIndex('updated_by', 'ferrymen', 'updated_by');

        $this->addForeignKey('fk_ferrymen_created_by', 'ferrymen', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_ferrymen_updated_by', 'ferrymen', 'updated_by', 'user', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_ferrymen_updated_by', 'ferrymen');
        $this->dropForeignKey('fk_ferrymen_created_by', 'ferrymen');

        $this->dropIndex('updated_by', 'ferrymen');
        $this->dropIndex('created_by', 'ferrymen');

        $this->dropColumn('ferrymen', 'updated_by');
        $this->dropColumn('ferrymen', 'updated_at');
        $this->dropColumn('ferrymen', 'created_by');
        $this->dropColumn('ferrymen', 'created_at');
    }
}
