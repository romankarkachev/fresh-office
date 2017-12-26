<?php

use yii\db\Migration;

/**
 * Добавляется поле "Тип" в таблицу временного хранилища обработанных папок.
 */
class m171214_083925_adding_type_to_tmp_enum_files extends Migration
{
    public function up()
    {
        $this->addColumn('tmp_enum_files', 'type', 'TINYINT(1) NOT NULL DEFAULT "1" COMMENT"Тип (1 - папка обработана, 2 - постоянный игнор)"');
    }

    public function down()
    {
        $this->dropColumn('tmp_enum_files', 'type');
    }
}
