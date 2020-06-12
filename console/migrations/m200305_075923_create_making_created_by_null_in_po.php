<?php

use yii\db\Migration;

/**
 * Поле "Автор создания" становится необязательным в платежных ордерах по бюджету.
 */
class m200305_075923_create_making_created_by_null_in_po extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(\common\models\Po::tableName(), 'created_by', $this->integer()->comment('Автор создания'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
