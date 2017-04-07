<?php

use yii\db\Migration;

class m170403_060539_adding_new_users_roles extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('role_documents');
        $role->description = 'Работа с документами';
        Yii::$app->authManager->add($role);
        unset($role);

        $role = Yii::$app->authManager->createRole('role_report1');
        $role->description = 'Работа с отчетом по обороту';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('role_documents');
        Yii::$app->authManager->remove($role);
        unset($role);

        $role = Yii::$app->authManager->getRole('role_report1');
        Yii::$app->authManager->remove($role);
    }
}
