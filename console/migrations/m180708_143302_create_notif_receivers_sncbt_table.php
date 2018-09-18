<?php

use yii\db\Migration;

/**
 * Создается таблица для хранения E-mail-адресов получателей уведомлений о том, что статусы проектов не меняется определенное администратором время.
 */
class m180708_143302_create_notif_receivers_sncbt_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Получатели уведомлений по E-mail о том, что статусы проектов продолжительное время не меняется (states not changed for a long time)"';
        };

        $this->createTable('notif_receivers_sncbt', [
            'id' => $this->primaryKey(),
            'state_id' => $this->integer()->notNull()->comment('Статус проектов для отслеживания'),
            'time' => $this->integer()->notNull()->comment('Время в минутах (сколько статус не меняется уже)'),
            'receiver' => $this->string()->comment('E-mail'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('notif_receivers_sncbt');
    }
}
