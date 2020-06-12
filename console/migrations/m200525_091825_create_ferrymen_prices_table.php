<?php

use yii\db\Migration;

/**
 * Создается таблица "Цены перевозчиков".
 */
class m200525_091825_create_ferrymen_prices_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    const TABLE_NAME = 'ferrymen_prices';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_FERRYMAN_ID_NAME = 'ferryman_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_FERRYMAN_ID_NAME = 'fk_' . self::TABLE_NAME . '_' . self::FIELD_FERRYMAN_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Цены перевозчиков"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'ferryman_id' => $this->integer()->notNull()->comment('Перевозчик'),
            'price' => $this->decimal(12,2)->comment('Стоимость'),
            'cost' => $this->decimal(12,2)->comment('Себестоимость'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_FERRYMAN_ID_NAME, self::TABLE_NAME, self::FIELD_FERRYMAN_ID_NAME);

        $this->addForeignKey(self::FK_FERRYMAN_ID_NAME, self::TABLE_NAME, self::FIELD_FERRYMAN_ID_NAME, 'ferrymen', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_FERRYMAN_ID_NAME, self::TABLE_NAME);

        $this->dropIndex(self::FIELD_FERRYMAN_ID_NAME, self::TABLE_NAME);

        $this->dropTable(self::TABLE_NAME);
    }
}
