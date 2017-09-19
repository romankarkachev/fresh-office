<?php

use yii\db\Migration;
use common\models\ProjectsStates;

/**
 * Добавляются несколько статусов проектов.
 */
class m170918_222842_adding_to_projects_states extends Migration
{
    public function up()
    {
        $exists = ProjectsStates::findOne(13);
        if ($exists == null)
            $this->insert('projects_states', [
                'name' => 'Вывоз завершен',
                'id' => 13,
            ]);

        $exists = ProjectsStates::findOne(14);
        if ($exists == null)
            $this->insert('projects_states', [
                'name' => 'Одобрено производством',
                'id' => 14,
            ]);

        $exists = ProjectsStates::findOne(15);
        if ($exists == null)
            $this->insert('projects_states', [
                'name' => 'Несовпадение',
                'id' => 15,
            ]);
    }

    public function down()
    {
        return true;
    }
}
