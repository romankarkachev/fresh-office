<?php

use yii\db\Migration;

/**
 * Создается таблица "Виды доставки корреспонденции".
 */
class m170730_113339_create_post_delivery_kinds_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Виды доставки корреспонденции"';
        };

        $this->createTable('post_delivery_kinds', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('post_delivery_kinds', [
            'id' => 1,
            'name' => 'Самовывоз',
        ]);

        $this->insert('post_delivery_kinds', [
            'id' => 2,
            'name' => 'Курьер',
        ]);

        $this->insert('post_delivery_kinds', [
            'id' => 3,
            'name' => 'Почта РФ',
        ]);

        $this->insert('post_delivery_kinds', [
            'id' => 4,
            'name' => 'Major Express',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('post_delivery_kinds');
    }
}
