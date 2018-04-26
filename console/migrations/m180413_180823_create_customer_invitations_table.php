<?php

use yii\db\Migration;

/**
 * Создается таблица "Приглашения клиентов для работы в личном кабинете".
 */
class m180413_180823_create_customer_invitations_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Приглашения клиентов для работы в личном кабинете"';
        };

        $this->createTable('customer_invitations', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время отправки'),
            'created_by' => $this->integer()->comment('Отправитель'),
            'fo_ca_id' => $this->integer()->notNull()->comment('Контрагент'),
            'email' => $this->string()->notNull()->comment('E-mail'),
            'token' => $this->string(32)->notNull()->comment('Токен'),
            'expires_at' => $this->integer()->notNull()->comment('Срок годности токена'),
            'user_id' => $this->integer()->unique()->comment('Пользователь системы'),
        ], $tableOptions);

        $this->createIndex('created_by', 'customer_invitations', 'created_by');
        $this->createIndex('token', 'customer_invitations', 'token', true);

        $this->addForeignKey('fk_customer_invitations_created_by', 'customer_invitations', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_customer_invitations_user_id', 'customer_invitations', 'user_id', 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_customer_invitations_user_id', 'customer_invitations');
        $this->dropForeignKey('fk_customer_invitations_created_by', 'customer_invitations');

        $this->dropIndex('user_id', 'customer_invitations');
        $this->dropIndex('token', 'customer_invitations');
        $this->dropIndex('created_by', 'customer_invitations');

        $this->dropTable('customer_invitations');
    }
}
