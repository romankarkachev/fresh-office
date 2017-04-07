<?php

use yii\db\Migration;

/**
 * Создается таблица "Обращения (заявки с сайтов)".
 */
class m170406_081723_create_appeals_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Обращения (заявки с сайтов)"';
        }

        $this->createTable('appeals', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'form_username' => $this->string(150)->comment('Поле формы Имя'),
            'form_region' => $this->string(150)->comment('Поле формы Регион'),
            'form_phone' => $this->string(150)->comment('Поле формы Телефон'),
            'form_email' => $this->string(150)->comment('Поле формы Email'),
            'form_message' => $this->text()->comment('Поле формы Текст сообщения'),
            'fo_id_company' => $this->integer()->unsigned()->comment('Контрагент из Fresh Office'),
            'fo_company_name' => $this->string(150)->comment('Наименование контрагента из Fresh Office'),
            'ca_state_id' => $this->integer()->comment('Статус контрагента (0 - новый, 1 - действующий), определяется по оплате'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('appeals');
    }
}
