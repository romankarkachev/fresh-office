<?php

use yii\db\Migration;

/**
 * Роль Оператор переименовывается в Старшего оператора, а также создается роль Оператор.
 */
class m171204_094447_insert_new_users_roles extends Migration
{
    public function up()
    {
        $roleHead = Yii::$app->authManager->getRole('operator');
        $roleHead->name = 'operator_head';
        $roleHead->description = 'Старший оператор';
        $roleHead->updatedAt = time();
        Yii::$app->authManager->update('operator', $roleHead);

        $role = Yii::$app->authManager->createRole('operator');
        $role->description = 'Оператор';
        Yii::$app->authManager->add($role);

        // включаем роль оператора в старшего оператора
        Yii::$app->authManager->addChild($roleHead, $role);
    }

    /**
     * Возвращаем старших операторов в роль оператора обычного (operatorus usualus).
     */
    public function down()
    {
        print Yii::$app->db->createCommand()->update('auth_assignment', [
            'item_name' => 'operator',
        ], [
            'item_name' => 'operator_head',
        ])->execute();

        $role = Yii::$app->authManager->getRole('operator_head');
        Yii::$app->authManager->remove($role);
    }
}
