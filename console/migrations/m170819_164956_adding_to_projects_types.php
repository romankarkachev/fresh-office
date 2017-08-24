<?php

use yii\db\Migration;

/**
 * Добавляется тип проектов.
 */
class m170819_164956_adding_to_projects_types extends Migration
{
    public function up()
    {
        $exists = \common\models\ProjectsTypes::findOne(15);
        if ($exists == null)
            $this->insert('projects_types', [
                'name' => 'Документы постоплата',
                'id' => 15,
            ]);
    }

    public function down()
    {
        return true;
    }
}
