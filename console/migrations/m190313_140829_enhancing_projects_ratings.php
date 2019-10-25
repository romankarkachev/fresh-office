<?php

use yii\db\Migration;

/**
 * Добавляется поле "Токен" для голосования неавторизованными пользователями извне, некоторые поля становятся необязательными.
 */
class m190313_140829_enhancing_projects_ratings extends Migration
{
    const TABLE_NAME = 'projects_ratings';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(self::TABLE_NAME, 'rate', $this->decimal(5,2)->comment('Оценка'));
        $this->addColumn(self::TABLE_NAME, 'token', $this->string(32)->comment('Токен для голосования неавторизованными пользователями'));
        $this->addColumn(self::TABLE_NAME, 'email', $this->string()->comment('E-mail контактного лица, которому отправляется приглашение поставить оценку'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'email');
        $this->dropColumn(self::TABLE_NAME, 'token');
        $this->alterColumn(self::TABLE_NAME, 'rate', $this->decimal(5,2)->notNull()->comment('Оценка'));
    }
}
