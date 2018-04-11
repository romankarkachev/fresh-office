<?php

use yii\db\Migration;

/**
 * Добавляется роль "Перевозчик" для работы в собственной отдельной подсистеме.
 */
class m180314_212134_insert_users_roles extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('ferryman');
        $role->description = 'Перевозчик';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('ferryman');
        Yii::$app->authManager->remove($role);
    }
}
