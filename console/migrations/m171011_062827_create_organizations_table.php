<?php

use yii\db\Migration;

/**
 * Создается таблица "Организации".
 */
class m171011_062827_create_organizations_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Организации"';
        };

        $this->createTable('organizations', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->comment('Наименование'),
            'name_short' => $this->string()->notNull()->comment('Сокращенное наименование'),
            'name_full' => $this->string()->notNull()->comment('Полное наименование'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('organizations');
    }
}
