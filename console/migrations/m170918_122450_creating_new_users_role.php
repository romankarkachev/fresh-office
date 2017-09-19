<?php

use yii\db\Migration;

/**
 * Добавляется новая роль пользователей.
 */
class m170918_122450_creating_new_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('prod_department_head');
        $role->description = 'Старший смены на производстве';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('prod_department_head');
        Yii::$app->authManager->remove($role);
    }
}
