<?php

use yii\db\Migration;

/**
 * Добавляется колонка "Источник обращения" в таблицу обращений.
 */
class m170408_080121_adding_appeal_source_to_appeals extends Migration
{
    public function up()
    {
        $this->addColumn('appeals', 'as_id', $this->integer()->comment('Источник обращения'));

        $this->createIndex('as_id', 'appeals', 'as_id');

        $this->addForeignKey('fk_appeals_as_id', 'appeals', 'as_id', 'appeal_sources', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_appeals_as_id', 'appeals');

        $this->dropIndex('as_id', 'appeals');

        $this->dropColumn('appeals', 'as_id');
    }
}
