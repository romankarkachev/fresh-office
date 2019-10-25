<?php

use yii\db\Migration;

/**
 * Добавляется роль помощника (имеющего доступ к бюджетным платежным ордерам).
 */
class m190603_171137_insert_users_role extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $role = Yii::$app->authManager->createRole('assistant');
        $role->description = 'Помощник';
        Yii::$app->authManager->add($role);

        $role = Yii::$app->authManager->createRole('accountant_b');
        $role->description = 'Бухгалтер по бюджету';
        Yii::$app->authManager->add($role);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $role = Yii::$app->authManager->getRole('assistant');
        Yii::$app->authManager->remove($role);

        $role = Yii::$app->authManager->getRole('accountant_b');
        Yii::$app->authManager->remove($role);
    }
}
