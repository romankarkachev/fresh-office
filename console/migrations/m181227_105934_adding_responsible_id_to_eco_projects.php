<?php

use yii\db\Migration;

/**
 * Добавляется поле "Ответственный" в таблицу проектов по экологии.
 */
class m181227_105934_adding_responsible_id_to_eco_projects extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('eco_projects', 'responsible_id', $this->integer()->comment('Ответственный') . ' AFTER `created_by`');

        $this->createIndex('responsible_id', 'eco_projects', 'responsible_id');

        $this->addForeignKey('fk_eco_projects_responsible_id', 'eco_projects', 'responsible_id', 'user', 'id');

        // скопируем авторов проектов в ответственных
        foreach (\common\models\EcoProjects::find()->all() as $ecoProject) {
            $ecoProject->updateAttributes([
                'responsible_id' => $ecoProject->created_by,
            ]);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_eco_projects_responsible_id', 'eco_projects');

        $this->dropIndex('responsible_id', 'eco_projects');

        $this->dropColumn('eco_projects', 'responsible_id');
    }
}
