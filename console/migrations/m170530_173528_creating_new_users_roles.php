<?php

use yii\db\Migration;

class m170530_173528_creating_new_users_roles extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('logist');
        $role->description = 'Логист';
        Yii::$app->authManager->add($role);
        unset($role);

        $role = Yii::$app->authManager->createRole('dpc_head');
        $role->description = 'Руководитель ЦОД';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('dpc_head');
        Yii::$app->authManager->remove($role);
        unset($role);

        $role = Yii::$app->authManager->getRole('logist');
        Yii::$app->authManager->remove($role);
    }
}
