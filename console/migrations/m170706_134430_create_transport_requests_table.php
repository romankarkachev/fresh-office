<?php

use yii\db\Migration;

/**
 * Создается таблица "Запросы на транспорт".
 */
class m170706_134430_create_transport_requests_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Запросы на транспорт"';
        };

        $this->createTable('transport_requests', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'created_by' => $this->integer()->notNull()->comment('Автор создания'),
            'finished_at' => $this->integer()->comment('Дата и время закрытия заявки'),
            'customer_id' => $this->integer()->comment('Контрагент'),
            'customer_name' => $this->string()->comment('Контрагент'),
            'region_id' => $this->integer()->unsigned()->notNull()->comment('Регион'),
            'city_id' => $this->integer()->unsigned()->notNull()->comment('Город'),
            'address' => $this->string()->comment('Адрес'),
            'state_id' => $this->integer()->notNull()->comment('Статус'),
            'comment_manager' => $this->text()->comment('Комментарий менеджера'),
            'comment_logist' => $this->text()->comment('Комментарий логиста'),
            'our_loading' => 'TINYINT(1) DEFAULT "0" COMMENT "Необходимость нашей погрузки (0 - нет, 1 - да)"',
            'periodicity_id' => $this->integer()->comment('Периодичность вывоза'),
            'special_conditions' => $this->text()->comment('Особые условия'),
            'spec_free' => 'TINYINT(1) DEFAULT "1" COMMENT "Наличие свободного подъезда (0 - нет, 1 - да)"',
            'spec_hose' => $this->string(50)->comment('Длина шланга'),
            'spec_cond' => $this->text()->comment('Особые условия'),
        ], $tableOptions);

        $this->createIndex('created_by', 'transport_requests', 'created_by');
        $this->createIndex('region_id', 'transport_requests', 'region_id');
        $this->createIndex('city_id', 'transport_requests', 'city_id');
        $this->createIndex('state_id', 'transport_requests', 'state_id');
        $this->createIndex('periodicity_id', 'transport_requests', 'periodicity_id');

        $this->addForeignKey('fk_transport_requests_created_by', 'transport_requests', 'created_by', 'user', 'id');
        $this->addForeignKey('fk_transport_requests_region_id', 'transport_requests', 'region_id', 'region', 'region_id');
        $this->addForeignKey('fk_transport_requests_city_id', 'transport_requests', 'city_id', 'city', 'city_id');
        $this->addForeignKey('fk_transport_requests_state_id', 'transport_requests', 'state_id', 'transport_requests_states', 'id');
        $this->addForeignKey('fk_transport_requests_periodicity_id', 'transport_requests', 'periodicity_id', 'periodicity_kinds', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_transport_requests_periodicity_id', 'transport_requests');
        $this->dropForeignKey('fk_transport_requests_state_id', 'transport_requests');
        $this->dropForeignKey('fk_transport_requests_city_id', 'transport_requests');
        $this->dropForeignKey('fk_transport_requests_region_id', 'transport_requests');
        $this->dropForeignKey('fk_transport_requests_created_by', 'transport_requests');

        $this->dropIndex('periodicity_id', 'transport_requests');
        $this->dropIndex('state_id', 'transport_requests');
        $this->dropIndex('city_id', 'transport_requests');
        $this->dropIndex('region_id', 'transport_requests');
        $this->dropIndex('created_by', 'transport_requests');

        $this->dropTable('transport_requests');
    }
}
