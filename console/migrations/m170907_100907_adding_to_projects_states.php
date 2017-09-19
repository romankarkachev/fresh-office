<?php

use yii\db\Migration;
use common\models\ProjectsStates;

/**
 * Добавляются несколько статусов проектов.
 */
class m170907_100907_adding_to_projects_states extends Migration
{
    public function up()
    {
        $exists = ProjectsStates::findOne(4);
        if ($exists == null)
            $this->insert('projects_states', [
                'name' => 'Счет ожидает оплаты',
                'id' => 4,
            ]);

        $exists = ProjectsStates::findOne(5);
        if ($exists == null)
            $this->insert('projects_states', [
                'name' => 'Оплачено',
                'id' => 5,
            ]);

        $exists = ProjectsStates::findOne(17);
        if ($exists == null)
            $this->insert('projects_states', [
                'name' => 'Закрытие счета',
                'id' => 17,
            ]);

    }

    public function down()
    {
        return true;
    }
}
