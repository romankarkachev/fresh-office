<?php

use yii\db\Migration;

/**
 * Создается таблица "Ответственные лица для новых контрагентов".
 */
class m170410_174906_create_responsible_for_new_ca_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Ответственные лица для новых контрагентов"';
        }

        $this->createTable('responsible_for_new_ca', [
            'id' => $this->primaryKey(),
            'responsible_id' => $this->integer()->notNull()->comment('Ответственный'),
            'responsible_name' => $this->string(50)->notNull()->comment('Ответственный'),
        ], $tableOptions);

        $this->insert('responsible_for_new_ca', [
            'responsible_id' => 86,
            'responsible_name' => 'Власова Светлана',
        ]);

        $this->insert('responsible_for_new_ca', [
            'responsible_id' => 60,
            'responsible_name' => 'Каюмова Зульфия',
        ]);

        $this->insert('responsible_for_new_ca', [
            'responsible_id' => 41,
            'responsible_name' => 'Яроцкая Наталья',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('responsible_for_new_ca');
    }
}
