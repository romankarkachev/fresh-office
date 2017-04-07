<?php

use yii\db\Migration;

/**
 * Создается таблица "Ответственные лица".
 */
class m170406_175625_create_responsible_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Ответственные лица"';
        }

        $this->createTable('responsible', [
            'id' => $this->primaryKey(),
            'required_id' => $this->integer()->notNull()->comment('Искомый ответственный'),
            'required_name' => $this->string(50)->notNull()->comment('Искомый ответственный'),
            'substitute_id' => $this->integer()->notNull()->comment('Заменяющий ответственный'),
            'substitute_name' => $this->string(50)->notNull()->comment('Заменяющий ответственный'),
        ], $tableOptions);

        $this->insert('responsible', [
            'required_id' => 40,
            'required_name' => 'ЦОД',
            'substitute_id' => 49,
            'substitute_name' => 'Текучева Елена',
        ]);

        $this->insert('responsible', [
            'required_id' => 72,
            'required_name' => 'ВИП',
            'substitute_id' => 90,
            'substitute_name' => 'Лисунова Татьяна',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('responsible');
    }
}
