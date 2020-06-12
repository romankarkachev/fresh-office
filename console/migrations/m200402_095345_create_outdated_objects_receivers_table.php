<?php

use yii\db\Migration;

/**
 * Создается таблица "Получатели уведомлений о просроченных по тем или иным показателям объектах".
 */
class m200402_095345_create_outdated_objects_receivers_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'outdated_objects_receivers';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Получатели уведомлений о просроченных по тем или иным показателям объектах"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'section' => 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Раздел учета (1 - проекты по экологии, 2 - договоры по экологии, 3 - запросы на транспорт, 4 - пакеты корреспонденции)"',
            'time' => $this->integer()->comment('Время в секундах (сколько статус не меняется уже)'),
            'receiver' => $this->string()->comment('E-mail'),
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
