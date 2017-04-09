<?php

use yii\db\Migration;

/**
 * Создается таблица "Ответственные лица для перевода в отказ".
 */
class m170409_073629_create_responsible_refusal_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Ответственные лица для перевода в отказ"';
        }

        $this->createTable('responsible_refusal', [
            'id' => $this->primaryKey(),
            'responsible_id' => $this->integer()->notNull()->comment('Ответственный'),
            'responsible_name' => $this->string(50)->notNull()->comment('Ответственный'),
        ], $tableOptions);

        $this->insert('responsible_refusal', [
            'responsible_id' => 3,
            'responsible_name' => 'БАНК',
        ]);

        $this->insert('responsible_refusal', [
            'responsible_id' => 46,
            'responsible_name' => 'БАНК ВХОДЯЩИЕ',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('responsible_refusal');
    }
}
