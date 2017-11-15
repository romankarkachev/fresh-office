<?php

use yii\db\Migration;

/**
 * Добавляется роль исключительно для загрузки файлов сканов лицензий.
 */
class m171108_190001_creating_new_users_role extends Migration
{
    public function up()
    {
        $role = Yii::$app->authManager->createRole('licenses_upload');
        $role->description = 'Загрузка файлов сканов лицензий';
        Yii::$app->authManager->add($role);
    }

    public function down()
    {
        $role = Yii::$app->authManager->getRole('licenses_upload');
        Yii::$app->authManager->remove($role);
    }
}
