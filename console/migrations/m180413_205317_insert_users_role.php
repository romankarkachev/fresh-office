<?php

use yii\db\Migration;

/**
 * Добавляется роль "Заказчик" для работы в собственной отдельной подсистеме.
 */
class m180413_205317_insert_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('customer');
        $role->description = 'Заказчик';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('customer');
        Yii::$app->authManager->remove($role);
    }
}
