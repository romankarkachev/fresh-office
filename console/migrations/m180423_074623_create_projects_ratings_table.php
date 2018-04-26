<?php

use yii\db\Migration;

/**
 * Создается таблица "Рейтинги проектов".
 */
class m180423_074623_create_projects_ratings_table extends Migration
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

        $this->createTable('projects_ratings', [
            'id' => $this->primaryKey(),
            'rated_at' => $this->integer()->notNull()->comment('Дата и время оценки'),
            'rated_by' => $this->integer()->notNull()->comment('Кто оценил'),
            'ca_id' => $this->integer()->comment('Контрагент'),
            'project_id' => $this->integer()->notNull()->comment('Проект'),
            'rate' => $this->decimal(5,2)->notNull()->comment('Оценка'),
            'comment' => $this->text()->comment('Замечания для не самой наивысшей оценки'),
        ], $tableOptions);

        $this->createIndex('rated_by', 'projects_ratings', 'rated_by');

        $this->addForeignKey('fk_projects_ratings_rated_by', 'projects_ratings', 'rated_by', 'user', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_projects_ratings_rated_by', 'projects_ratings');

        $this->dropIndex('rated_by', 'projects_ratings');

        $this->dropTable('projects_ratings');
    }
}
