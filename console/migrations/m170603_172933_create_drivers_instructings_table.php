<?php

use yii\db\Migration;

/**
 * Создается таблица "Инструктажи водителей".
 */
class m170603_172933_create_drivers_instructings_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Инструктажи водителей"';
        };

        $this->createTable('drivers_instructings', [
            'id' => $this->primaryKey(),
            'driver_id' => $this->integer()->notNull()->comment('Водитель'),
            'instructed_at' => $this->date()->notNull()->comment('Дата проведения'),
            'place' => $this->string(50)->comment('Место проведения'),
            'responsible' => $this->string(50)->comment('Ответственный'),
        ], $tableOptions);

        $this->createIndex('driver_id', 'drivers_instructings', 'driver_id');

        $this->addForeignKey('fk_drivers_instructings_driver_id', 'drivers_instructings', 'driver_id', 'drivers', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_drivers_instructings_driver_id', 'drivers_instructings');

        $this->dropIndex('driver_id', 'drivers_instructings');

        $this->dropTable('drivers_instructings');
    }
}
