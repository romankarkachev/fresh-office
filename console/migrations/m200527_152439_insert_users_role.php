<?php

use yii\db\Migration;

/**
 * Добавляется роль "Бухгалтер по зарплате".
 */
class m200527_152439_insert_users_role extends Migration
{
    /**
     * @var string наименование роли, которая добавляется
     */
    const ROLE_NAME = 'accountant_s';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $role = Yii::$app->authManager->createRole(self::ROLE_NAME);
        $role->description = 'Бухгалтер по з/п';
        Yii::$app->authManager->add($role);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $role = Yii::$app->authManager->getRole(self::ROLE_NAME);
        Yii::$app->authManager->remove($role);
    }
}
