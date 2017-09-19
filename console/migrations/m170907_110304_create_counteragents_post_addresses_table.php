<?php

use yii\db\Migration;

/**
 * Создается таблица "Почтовые адреса контрагентов".
 */
class m170907_110304_create_counteragents_post_addresses_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Почтовые адреса контрагентов"';
        };

        $this->createTable('counteragents_post_addresses', [
            'id' => $this->primaryKey(),
            'counteragent_id' => $this->integer()->notNull()->comment('Контрагент'),
            'src_id' => $this->integer()->notNull()->comment('ID в источнике'),
            'src_address' => $this->text()->notNull()->comment('Почтовый адрес из источника'),
            'zip_code' => $this->string(10)->comment('Почтовый индекс'),
            'address_m' => $this->text()->comment('Нормализованный почтовый адрес'),
            'comment' => $this->text()->comment('Примечание к адресу'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('counteragents_post_addresses');
    }
}
