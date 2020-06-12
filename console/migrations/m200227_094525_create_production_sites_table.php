<?php

use yii\db\Migration;

/**
 * Создается таблица "Производственные площадки".
 */
class m200227_094525_create_production_sites_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'production_sites';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Производственные площадки"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->comment('Наименование'),
            'fo_ca_id' => $this->integer()->comment('Контрагент в CRM'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
