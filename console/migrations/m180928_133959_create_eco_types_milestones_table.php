<?php

use yii\db\Migration;

/**
 * Создается таблица "Этапы по типам проектов".
 */
class m180928_133959_create_eco_types_milestones_table extends Migration
{
    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля type_id
     */
    private $fkTypeIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля milestone_id
     */
    private $fkMilestoneIdName;

    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_TYPE_ID_NAME = 'type_id';

    /**
     * Наименование поля, которое имеет внешний ключ
     */
    const FIELD_MILESTONE_ID_NAME = 'milestone_id';

    /**
     * Наименования этапов для добавления их в цикле
     */
    const MILESTONES_NAMES = [
        1 => 'Сбор информации от Заказчика',
        2 => 'Формирование пакета документов на согласование',
        3 => 'Согласование Заказчиком',
        4 => 'Получение согласованных Лимитов',
        5 => 'Подписание акта выполненных работ',
        6 => 'Оплата проекта',
        7 => 'Получение фоновой и климатической справок',
        8 => 'Получение Экспертного заключения',
        9 => 'Получение Санитарно-эпидемиологического заключения',
        10 => 'Получение согласованных Нормативов',
        11 => 'Согласование Мероприятий при НМУ',
        12 => 'Получение Разрешения на выбросы',
        13 => 'Получение фоновых концентраций и гидрологических характеристик',
        14 => 'Получение Решения о предоставлении водного объекта в пользование',
        15 => 'Оформление проекта НДС и согласование с Заказчиком',
        16 => 'Получение Разрешения на сброс',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'eco_types_milestones';
        $this->fkTypeIdName = 'fk_' . $this->tableName . '_' . self::FIELD_TYPE_ID_NAME;
        $this->fkMilestoneIdName = 'fk_' . $this->tableName . '_' . self::FIELD_MILESTONE_ID_NAME;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        // добавляем этапы
        foreach (self::MILESTONES_NAMES as $index => $milestone) {
            $this->insert('eco_milestones', [
                'id' => $index,
                'name' => $milestone,
            ]);
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Этапы по типам проектов"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_TYPE_ID_NAME => $this->integer()->notNull()->comment('Тип проекта'),
            self::FIELD_MILESTONE_ID_NAME => $this->integer()->notNull()->comment('Этап проекта'),
            'is_file_reqiured' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Требуется ли предоставление любого файла для закрытия этапа"',
            'is_affects_to_cycle_time' => 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Влияет ли на расчет общей продолжительности для завершения проекта"',
            'time_to_complete_required' => $this->smallInteger(6)->comment('Время для завершения этапа в днях'),
            'order_no' => $this->integer()->notNull()->defaultValue(0)->comment('Номер по порядку'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_TYPE_ID_NAME, $this->tableName, self::FIELD_TYPE_ID_NAME);
        $this->createIndex(self::FIELD_MILESTONE_ID_NAME, $this->tableName, self::FIELD_MILESTONE_ID_NAME);

        $this->addForeignKey($this->fkTypeIdName, $this->tableName, self::FIELD_TYPE_ID_NAME, 'eco_types', 'id');
        $this->addForeignKey($this->fkMilestoneIdName, $this->tableName, self::FIELD_MILESTONE_ID_NAME, 'eco_milestones', 'id');

        // ПНООЛР
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 1,
            self::FIELD_MILESTONE_ID_NAME => 1,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 15,
            'order_no' => 1,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 1,
            self::FIELD_MILESTONE_ID_NAME => 2,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 20,
            'order_no' => 2,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 1,
            self::FIELD_MILESTONE_ID_NAME => 3,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 10,
            'order_no' => 3,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 1,
            self::FIELD_MILESTONE_ID_NAME => 4,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 45,
            'order_no' => 4,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 1,
            self::FIELD_MILESTONE_ID_NAME => 5,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 10,
            'order_no' => 5,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 1,
            self::FIELD_MILESTONE_ID_NAME => 6,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 0,
            'time_to_complete_required' => 10,
            'order_no' => 6,
        ]);

        // ПДВ
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 1,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 15,
            'order_no' => 1,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 7,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 30,
            'order_no' => 2,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 2,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 15,
            'order_no' => 3,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 3,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 10,
            'order_no' => 4,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 8,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 65,
            'order_no' => 5,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 9,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 45,
            'order_no' => 6,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 10,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 45,
            'order_no' => 7,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 11,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 45,
            'order_no' => 8,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 12,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 45,
            'order_no' => 9,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 5,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 10,
            'order_no' => 10,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 2,
            self::FIELD_MILESTONE_ID_NAME => 6,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 0,
            'time_to_complete_required' => 10,
            'order_no' => 11,
        ]);

        // СЗЗ
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 3,
            self::FIELD_MILESTONE_ID_NAME => 1,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 15,
            'order_no' => 1,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 3,
            self::FIELD_MILESTONE_ID_NAME => 2,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 30,
            'order_no' => 2,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 3,
            self::FIELD_MILESTONE_ID_NAME => 3,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 10,
            'order_no' => 3,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 3,
            self::FIELD_MILESTONE_ID_NAME => 8,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 65,
            'order_no' => 4,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 3,
            self::FIELD_MILESTONE_ID_NAME => 9,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 45,
            'order_no' => 5,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 3,
            self::FIELD_MILESTONE_ID_NAME => 5,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 10,
            'order_no' => 6,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 3,
            self::FIELD_MILESTONE_ID_NAME => 6,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 0,
            'time_to_complete_required' => 10,
            'order_no' => 7,
        ]);

        // НДС
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 4,
            self::FIELD_MILESTONE_ID_NAME => 1,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 15,
            'order_no' => 1,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 4,
            self::FIELD_MILESTONE_ID_NAME => 13,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 30,
            'order_no' => 2,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 4,
            self::FIELD_MILESTONE_ID_NAME => 2,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 25,
            'order_no' => 3,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 4,
            self::FIELD_MILESTONE_ID_NAME => 3,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 10,
            'order_no' => 4,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 4,
            self::FIELD_MILESTONE_ID_NAME => 14,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 60,
            'order_no' => 5,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 4,
            self::FIELD_MILESTONE_ID_NAME => 15,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 25,
            'order_no' => 6,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 4,
            self::FIELD_MILESTONE_ID_NAME => 16,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 90,
            'order_no' => 7,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 4,
            self::FIELD_MILESTONE_ID_NAME => 5,
            'is_file_reqiured' => 1,
            'is_affects_to_cycle_time' => 1,
            'time_to_complete_required' => 10,
            'order_no' => 8,
        ]);
        $this->insert($this->tableName, [
            self::FIELD_TYPE_ID_NAME => 4,
            self::FIELD_MILESTONE_ID_NAME => 6,
            'is_file_reqiured' => 0,
            'is_affects_to_cycle_time' => 0,
            'time_to_complete_required' => 10,
            'order_no' => 9,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey($this->fkMilestoneIdName, $this->tableName);
        $this->dropForeignKey($this->fkTypeIdName, $this->tableName);

        $this->dropIndex(self::FIELD_MILESTONE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_TYPE_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);

        $this->truncateTable('eco_milestones');
    }
}
