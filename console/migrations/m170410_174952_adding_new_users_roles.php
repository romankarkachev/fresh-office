<?php

use yii\db\Migration;

/**
 * Добавляется роль "Оператор", под которой пользователь будет создавать новые обращения вручную.
 */
class m170410_174952_adding_new_users_roles extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('operator');
        $role->description = 'Оператор';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('operator');
        Yii::$app->authManager->remove($role);
    }
}
