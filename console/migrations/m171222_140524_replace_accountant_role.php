<?php

use yii\db\Migration;

/**
 * Роль "Импорт оплат" заменяется ролью "Бухгалтер".
 */
class m171222_140524_replace_accountant_role extends Migration
{
    public function up()
    {
        print Yii::$app->db->createCommand()->update('auth_assignment', [
            'item_name' => 'accountant',
        ], [
            'item_name' => 'accountant_freights',
        ])->execute();

        $role = Yii::$app->authManager->getRole('accountant_freights');
        if ($role != null) Yii::$app->authManager->remove($role);
    }

    public function down()
    {
        return true;
    }
}
