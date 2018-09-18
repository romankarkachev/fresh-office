<?php

use yii\db\Migration;

/**
 * Добавляется роль для работы с телефонией.
 */
class m180820_104456_insert_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('pbx');
        $role->description = 'Телефония';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('pbx');
        Yii::$app->authManager->remove($role);
    }
}
