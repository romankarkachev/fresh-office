<?php

use yii\db\Migration;

/**
 * Добавляется роль специалиста по тендерам.
 */
class m191009_080518_insert_users_role extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $role = Yii::$app->authManager->createRole('tenders_manager');
        $role->description = 'Специалист по тендерам';
        Yii::$app->authManager->add($role);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $role = Yii::$app->authManager->getRole('tenders_manager');
        Yii::$app->authManager->remove($role);
    }
}
