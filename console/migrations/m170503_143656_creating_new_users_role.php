<?php

use yii\db\Migration;

class m170503_143656_creating_new_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('accountant_freights');
        $role->description = 'Оплата рейсов';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('accountant_freights');
        Yii::$app->authManager->remove($role);
    }
}
