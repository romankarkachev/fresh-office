<?php

use yii\db\Migration;

/**
 * Добавляется новая роль пользователей. Будет использоваться для просмотра файлов обратной связи от производства.
 */
class m170927_194039_creating_new_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('prod_feedback');
        $role->description = 'Просмотр файлов обратной связи от производства';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('prod_feedback');
        Yii::$app->authManager->remove($role);
    }
}
