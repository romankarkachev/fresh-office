<?php

use yii\db\Migration;

/**
 * Добавляется роль для доступа к делопроизводству.
 */
class m181008_112220_insert_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('edf');
        $role->description = 'Делопроизводство';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('edf');
        Yii::$app->authManager->remove($role);
    }
}
