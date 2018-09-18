<?php

use yii\db\Migration;

/**
 * Создается таблица, в которой будут храниться координаты, присланные из мобильного приложения.
 */
class m180914_083816_create_mobile_app_geopos_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Рейтинги проектов"';
        };

        $this->createTable('mobile_app_geopos', [
            'id' => $this->primaryKey(),
            'arrived_at' => $this->integer()->notNull()->comment('Дата и время отправки координат'),
            'user_id' => $this->integer()->notNull()->comment('Чьи координаты'),
            'coord_lat' => $this->double()->notNull()->comment('Широта'),
            'coord_long' => $this->double()->notNull()->comment('Долгота'),
        ], $tableOptions);

        $this->createIndex('user_id', 'mobile_app_geopos', 'user_id');

        $this->addForeignKey('fk_mobile_app_geopos_user_id', 'mobile_app_geopos', 'user_id', 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_mobile_app_geopos_user_id', 'mobile_app_geopos');

        $this->dropIndex('user_id', 'mobile_app_geopos');

        $this->dropTable('mobile_app_geopos');
    }
}
