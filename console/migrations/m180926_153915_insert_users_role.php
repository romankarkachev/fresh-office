<?php

use yii\db\Migration;

/**
 * Добавляется роль для работы водителей с мобильного приложения.
 */
class m180926_153915_insert_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('foreignDriver');
        $role->description = 'Водитель перевозчика';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('foreignDriver');
        Yii::$app->authManager->remove($role);
    }
}
