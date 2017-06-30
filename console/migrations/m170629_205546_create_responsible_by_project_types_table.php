<?php

use yii\db\Migration;

/**
 * Создается таблица "Ответственные по типам проектов".
 */
class m170629_205546_create_responsible_by_project_types_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Ответственные по типам проектов"';
        };

        $this->createTable('responsible_by_project_types', [
            'id' => $this->primaryKey(),
            'project_type_id' => $this->integer()->notNull()->comment('Тип проекта (id)'),
            'project_type_name' => $this->string()->notNull()->comment('Тип проекта(наименование)'),
            'receivers' => $this->text()->notNull()->comment('Получатели (по одному на строку)'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('responsible_by_project_types');
    }
}
