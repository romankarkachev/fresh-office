<?php

use yii\db\Migration;

/**
 * Добавляется поле fo_id_company в таблицу correspondence_packages. Будет хранить id контрагента в CRM FO.
 */
class m170818_141506_adding_fo_id_company_to_correspondence_packages extends Migration
{
    public function up()
    {
        $this->addColumn('correspondence_packages', 'fo_id_company', $this->integer()->unsigned()->comment('Контрагент из Fresh Office') . ' AFTER `fo_project_id`');

        $projects = \common\models\CorrespondencePackages::find()->all();
        foreach ($projects as $project) {
            /* @var $project \common\models\CorrespondencePackages */
            $crmProject = \common\models\foProjects::findOne($project->fo_project_id);
            if ($crmProject != null) {
                $project->fo_id_company = $crmProject->ID_COMPANY;
                $project->save();
            }
        }
    }

    public function down()
    {
        $this->dropColumn('correspondence_packages', 'fo_id_company');
    }
}
