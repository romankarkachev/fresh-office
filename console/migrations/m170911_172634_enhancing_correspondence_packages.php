<?php

use yii\db\Migration;

/**
 * Добавляются колонки "Ответственный" и "Признак создания вручную".
 */
class m170911_172634_enhancing_correspondence_packages extends Migration
{
    public function up()
    {
        $this->addColumn('correspondence_packages', 'is_manual', 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Признак создания вручную" AFTER `created_at`');

        $this->addColumn('correspondence_packages', 'manager_id', $this->integer()->comment('Ответственный'));

        $this->createIndex('manager_id', 'correspondence_packages', 'manager_id');

        $this->addForeignKey('fk_correspondence_packages_manager_id', 'correspondence_packages', 'manager_id', 'user', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_correspondence_packages_manager_id', 'correspondence_packages');

        $this->dropIndex('manager_id', 'correspondence_packages');

        $this->dropColumn('correspondence_packages', 'manager_id');

        $this->dropColumn('correspondence_packages', 'is_manual');
    }
}
