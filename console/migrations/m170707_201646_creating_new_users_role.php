<?php

use yii\db\Migration;

class m170707_201646_creating_new_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('sales_department_manager');
        $role->description = 'Менеджер отдела продаж';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('sales_department_manager');
        Yii::$app->authManager->remove($role);
    }
}
