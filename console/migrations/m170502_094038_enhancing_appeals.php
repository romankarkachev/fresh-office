<?php

use yii\db\Migration;

/**
 * Добавляются поля "Ответственный", "Раздел учета", "Автор создания". При успешной автоматической идентификации
 * проставляется значение в поле ответственный, а при создании - выбранный ответственный. При изменении перезапись
 * не производится, то есть запоминается только изначально назначенный.
 */
class m170502_094038_enhancing_appeals extends Migration
{
    public function up()
    {
        $this->addColumn('appeals', 'fo_id_manager', $this->integer()->comment('Ответственный из Fresh Office') . ' AFTER `fo_company_name`');

        $this->addColumn('appeals', 'ac_id', 'TINYINT(1) DEFAULT 1 COMMENT "Раздел учета (1 - утилизация, 2 - экология)" AFTER `state_id`');

        $this->addColumn('appeals', 'created_by', $this->integer()->comment('Автор создания') . ' AFTER `created_at');
        $this->createIndex('created_by', 'appeals', 'created_by');
        $this->addForeignKey('fk_appeals_created_by', 'appeals', 'created_by', 'user', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_appeals_created_by', 'appeals');
        $this->dropIndex('created_by', 'appeals');
        $this->dropColumn('appeals', 'created_by');

        $this->dropColumn('appeals', 'ac_id');

        $this->dropColumn('appeals', 'fo_id_manager');
    }
}
