<?php

use yii\db\Migration;

/**
 * Создается таблица "Водители".
 */
class m170603_172932_create_drivers_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Водители"';
        };

        $this->createTable('drivers', [
            'id' => $this->primaryKey(),
            'ferryman_id' => $this->integer()->notNull()->comment('Перевозчик'),
            'surname' => $this->string(50)->notNull()->comment('Фамилия'),
            'name' => $this->string(50)->notNull()->comment('Имя'),
            'patronymic' => $this->string(50)->comment('Отчество'),
            'driver_license' => $this->string(30)->notNull()->comment('Водительское удостоверение'),
            'driver_license_index' => $this->string(30)->notNull()->comment('Водительское удостоверение (для поиска)'),
            'phone' => $this->string(10)->notNull()->comment('Телефон'),
        ], $tableOptions);

        $this->createIndex('ferryman_id', 'drivers', 'ferryman_id');
        $this->createIndex('driver_license_index', 'drivers', 'driver_license_index');

        $this->addForeignKey('fk_drivers_ferryman_id', 'drivers', 'ferryman_id', 'ferrymen', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_drivers_ferryman_id', 'drivers');

        $this->dropIndex('driver_license_index', 'drivers');
        $this->dropIndex('ferryman_id', 'drivers');

        $this->dropTable('drivers');
    }
}
