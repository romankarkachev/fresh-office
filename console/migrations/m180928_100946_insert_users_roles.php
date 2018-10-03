<?php

use yii\db\Migration;

/**
 * Добавляются роли эколога и его босса.
 */
class m180928_100946_insert_users_roles extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('ecologist_head');
        $role->description = 'Начальник отдела экологии';
        Yii::$app->authManager->add($role);
        unset($role);

        $role = Yii::$app->authManager->createRole('ecologist');
        $role->description = 'Эколог';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('ecologist_head');
        Yii::$app->authManager->remove($role);
        unset($role);

        $role = Yii::$app->authManager->getRole('ecologist');
        Yii::$app->authManager->remove($role);
    }
}
