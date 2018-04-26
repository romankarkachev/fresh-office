<?php

use yii\db\Migration;

/**
 * Создается таблица "Проекты". Она будет дублировать соответствующую таблицу во Fresh Office с той лишь разницей,
 * что будет содержать идентифицированный код региона.
 */
class m180425_110103_create_projects_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('projects', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            'address' => $this->text()->comment('Адрес'),
            'data' => $this->text()->comment('Данные проекта'),
            'ferryman_origin' => $this->string(100)->comment('Перевозчик'),
            'comment' => $this->text()->comment('Примечания'),
            'region_id' => $this->integer()->unsigned()->comment('Регион'),
            'city_id' => $this->integer()->unsigned()->comment('Город'),
            'ferryman_id' => $this->integer()->comment('Перевозчик'),
        ]);

        $this->createIndex('region_id', 'projects', 'region_id');
        $this->createIndex('city_id', 'projects', 'city_id');
        $this->createIndex('ferryman_id', 'projects', 'ferryman_id');

        $this->addForeignKey('fk_projects_region_id', 'projects', 'region_id', 'region', 'region_id');
        $this->addForeignKey('fk_projects_city_id', 'projects', 'city_id', 'city', 'city_id');
        $this->addForeignKey('fk_projects_ferryman_id', 'projects', 'ferryman_id', 'ferrymen', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_projects_ferryman_id', 'projects');
        $this->dropForeignKey('fk_projects_city_id', 'projects');
        $this->dropForeignKey('fk_projects_region_id', 'projects');

        $this->dropIndex('ferryman_id', 'projects');
        $this->dropIndex('city_id', 'projects');
        $this->dropIndex('region_id', 'projects');

        $this->dropTable('projects');
    }
}
