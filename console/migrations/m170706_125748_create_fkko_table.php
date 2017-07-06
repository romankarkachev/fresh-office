<?php

use yii\db\Migration;

/**
 * Создается таблица "Коды ФККО".
 */
class m170706_125748_create_fkko_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Коды ФККО"';
        };

        $this->createTable('fkko', [
            'id' => $this->primaryKey(),
            'fkko_code' => $this->string(11)->notNull()->comment('Код по ФККО-2017'),
            'fkko_name' => $this->text()->notNull()->comment('Наименование'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('fkko');
    }
}
