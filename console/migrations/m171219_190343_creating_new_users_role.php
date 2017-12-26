<?php

use yii\db\Migration;

/**
 * Добавляется роль "Бухгалтер".
 */
class m171219_190343_creating_new_users_role extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $role = Yii::$app->authManager->createRole('accountant');
        $role->description = 'Бухгалтер';
        Yii::$app->authManager->add($role);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $role = Yii::$app->authManager->getRole('accountant');
        Yii::$app->authManager->remove($role);
    }
}
