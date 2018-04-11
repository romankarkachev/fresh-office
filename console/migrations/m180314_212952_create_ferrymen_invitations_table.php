<?php

use yii\db\Migration;

/**
 * Создается таблица "Приглашения перевозчиков для работы в личном кабинете".
 */
class m180314_212952_create_ferrymen_invitations_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Приглашения перевозчиков для работы в личном кабинете"';
        };

        $this->createTable('ferrymen_invitations', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'created_by' => $this->integer()->comment('Автор создания'),
            'ferryman_id' => $this->integer()->notNull()->comment('Перевозчик'),
            'email' => $this->string()->notNull()->comment('E-mail'),
            'token' => $this->string(32)->notNull()->comment('Токен'),
            'expires_at' => $this->integer()->notNull()->comment('Срок годности токена'),
        ], $tableOptions);

        $this->createIndex('created_by', 'ferrymen_invitations', 'created_by');
        $this->createIndex('ferryman_id', 'ferrymen_invitations', 'ferryman_id');
        $this->createIndex('token', 'ferrymen_invitations', 'token', true);

        $this->addForeignKey('fk_ferrymen_invitations_created_by', 'ferrymen_invitations', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_ferrymen_invitations_ferryman_id', 'ferrymen_invitations', 'ferryman_id', 'ferrymen', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_ferrymen_invitations_ferryman_id', 'ferrymen_invitations');
        $this->dropForeignKey('fk_ferrymen_invitations_created_by', 'ferrymen_invitations');

        $this->dropIndex('token', 'ferrymen_invitations');
        $this->dropIndex('ferryman_id', 'ferrymen_invitations');
        $this->dropIndex('created_by', 'ferrymen_invitations');

        $this->dropTable('ferrymen_invitations');
    }
}
