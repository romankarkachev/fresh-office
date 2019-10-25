<?php

use yii\db\Migration;

/**
 * Создается таблица "Тендерные площадки".
 */
class m190614_092139_create_tenders_platforms_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'tenders_platforms';

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Тендерные площадки"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->comment('Наименование'),
            'href' => $this->string()->comment('Адрес в сети'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
