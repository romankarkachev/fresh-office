<?php

use yii\db\Migration;

/**
 * Добавляется новая роль пользователей. Будет использоваться для просмотра файлов обратной связи от производства и
 * управления запросами на транспорт. На момент применения миграция предназначена для помощника руководителя.
 */
class m171003_085211_creating_new_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('head_assist');
        $role->description = 'Логистика, Файлы производства';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('head_assist');
        Yii::$app->authManager->remove($role);
    }
}
