<?php

use yii\db\Migration;

/**
 * Создается таблица для хранения E-mail-адресов получателей уведомлений о том, что статус проекта продолжительное время не меняется.
 */
class m180708_135106_create_notif_receivers_sncflt_table extends Migration
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

        $this->createTable('notif_receivers_sncflt', [
            'id' => $this->primaryKey(),
            'receiver' => $this->string()->comment('E-mail'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('notif_receivers_sncflt');
    }
}
