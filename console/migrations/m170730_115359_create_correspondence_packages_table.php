<?php

use yii\db\Migration;

/**
 * Создается таблица "Пакеты корреспонденции".
 */
class m170730_115359_create_correspondence_packages_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Пакеты корреспонденции"';
        };

        $this->createTable('correspondence_packages', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'ready_at' => $this->integer()->comment('Дата и время подготовки'),
            'sent_at' => $this->integer()->comment('Дата и время отправки'),
            'fo_project_id' => $this->integer()->comment('ID проекта'),
            'customer_name' => $this->string()->comment('Контрагент'),
            'state_id' => $this->integer()->comment('Статус'),
            'type_id' => $this->integer()->comment('Тип'),
            'pad' => $this->text()->comment('Виды документов'),
            'pd_id' => $this->integer()->comment('Способ доставки'),
            'track_num' => $this->string(50)->comment('Трек-номер'),
            'other' => $this->text()->comment('Другие документы'),
            'comment' => $this->text()->comment('Примечание'),
        ], $tableOptions);

        $this->createIndex('state_id', 'correspondence_packages', 'state_id');
        $this->createIndex('type_id', 'correspondence_packages', 'type_id');
        $this->createIndex('pd_id', 'correspondence_packages', 'pd_id');

        $this->addForeignKey('fk_correspondence_packages_state_id', 'correspondence_packages', 'state_id', 'projects_states', 'id');
        $this->addForeignKey('fk_correspondence_packages_type_id', 'correspondence_packages', 'type_id', 'projects_types', 'id');
        $this->addForeignKey('fk_correspondence_packages_pd_id', 'correspondence_packages', 'pd_id', 'post_delivery_kinds', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_correspondence_packages_pd_id', 'correspondence_packages');
        $this->dropForeignKey('fk_correspondence_packages_type_id', 'correspondence_packages');
        $this->dropForeignKey('fk_correspondence_packages_state_id', 'correspondence_packages');

        $this->dropIndex('pd_id', 'correspondence_packages');
        $this->dropIndex('type_id', 'correspondence_packages');
        $this->dropIndex('state_id', 'correspondence_packages');

        $this->dropTable('correspondence_packages');
    }
}
