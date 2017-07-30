<?php

use yii\db\Migration;

/**
 * Создается таблица "Статусы проектов".
 */
class m170730_103656_create_projects_states_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Статусы проектов"';
        };

        $this->createTable('projects_states', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('projects_states', [
            'name' => 'Отдано на отправку',
            'id' => 18,
        ]);

        $this->insert('projects_states', [
            'name' => 'Формирование документов на отправку',
            'id' => 38,
        ]);

        $this->insert('projects_states', [
            'name' => 'Ожидает отправки',
            'id' => 43,
        ]);

        $this->insert('projects_states', [
            'name' => 'Отправлено',
            'id' => 19,
        ]);

        $this->insert('projects_states', [
            'name' => 'Доставлено',
            'id' => 20,
        ]);

        $this->insert('projects_states', [
            'name' => 'Согласование вывоза',
            'id' => 6,
        ]);

        $this->insert('projects_states', [
            'name' => 'Завершено',
            'id' => 25,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('projects_states');
    }
}
