<?php

use yii\db\Migration;
use common\models\ProjectsTypes;

/**
 * Добавляются типы проектов.
 */
class m170913_095229_adding_to_projects_types extends Migration
{
    public function up()
    {
        $exists = ProjectsTypes::findOne(4);
        if ($exists == null)
            $this->insert('projects_types', [
                'name' => 'Вывоз',
                'id' => 4,
            ]);

        $exists = ProjectsTypes::findOne(6);
        if ($exists == null)
            $this->insert('projects_types', [
                'name' => 'Самопривоз',
                'id' => 6,
            ]);

        $exists = ProjectsTypes::findOne(7);
        if ($exists == null)
            $this->insert('projects_types', [
                'name' => 'Фото/Видео',
                'id' => 7,
            ]);

        $exists = ProjectsTypes::findOne(8);
        if ($exists == null)
            $this->insert('projects_types', [
                'name' => 'Выездные работы',
                'id' => 8,
            ]);

        $exists = ProjectsTypes::findOne(10);
        if ($exists == null)
            $this->insert('projects_types', [
                'name' => 'Производство',
                'id' => 10,
            ]);

        $exists = ProjectsTypes::findOne(11);
        if ($exists == null)
            $this->insert('projects_types', [
                'name' => 'Экология',
                'id' => 11,
            ]);

        $exists = ProjectsTypes::findOne(14);
        if ($exists == null)
            $this->insert('projects_types', [
                'name' => 'Осмотр объекта',
                'id' => 14,
            ]);
    }

    public function down()
    {
        return true;
    }
}
