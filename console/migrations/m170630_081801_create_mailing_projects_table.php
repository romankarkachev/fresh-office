<?php

use yii\db\Migration;

/**
 * Создается таблица "Проекты, уже отправленные в рассылке ответственным лицам".
 */
class m170630_081801_create_mailing_projects_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Проекты, уже отправленные в рассылке ответственным лицам"';
        };

        $this->createTable('mailing_projects', [
            'id' => $this->primaryKey(),
            'sent_at' => $this->integer()->notNull()->comment('Дата и время отправки'),
            'project_id' => $this->integer()->notNull()->comment('Проект'),
            'email_receiver' => $this->string()->notNull()->comment('E-mail получателя'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('mailing_projects');
    }
}
