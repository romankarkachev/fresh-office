<?php

use yii\db\Migration;

class m170503_140957_rename_report1_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->getRole('role_report1');
        $role->name = 'sales_department_head';
        $role->description = 'Начальник отдела продаж';
        $role->updatedAt = time();
        Yii::$app->authManager->update('role_report1', $role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('sales_department_head');
        $role->name = 'role_report1';
        $role->description = 'Работа с отчетом по обороту';
        $role->updatedAt = time();
        Yii::$app->authManager->update('sales_department_head', $role);
    }
}
