<?php

use yii\db\Migration;

/**
 * Переименовывается таблица "Ответственные лица".
 */
class m170409_073613_rename_responsible_table extends Migration
{
    public function up()
    {
        $this->renameTable('responsible', 'responsible_substitutes');
        $this->addCommentOnTable('responsible_substitutes', 'Ответственные лица для уведомления');
    }

    public function down()
    {
        $this->renameTable('responsible_substitutes', 'responsible');
        $this->addCommentOnTable('responsible', 'Ответственные лица');
    }
}
