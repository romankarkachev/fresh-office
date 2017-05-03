<?php

use yii\db\Migration;

class m170503_143656_creating_new_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('role_processes');
        $role->description = 'Работа с обработками';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('role_processes');
        Yii::$app->authManager->remove($role);
    }
}
