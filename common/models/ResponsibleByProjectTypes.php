<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "responsible_by_project_types".
 *
 * @property integer $id
 * @property integer $project_type_id
 * @property string $project_type_name
 * @property string $receivers
 */
class ResponsibleByProjectTypes extends \yii\db\ActiveRecord
{
    // набор типов проектов для робота с кодовым названием Zapier
    // запускается /services/mailing-by-projects-types
    const PROJECT_TYPES_ZAPIER = [
        ProjectsTypes::PROJECT_TYPE_ФОТО_ВИДЕО, // 7
        ProjectsTypes::PROJECT_TYPE_ВЫЕЗДНЫЕ_РАБОТЫ, // 8
        ProjectsTypes::PROJECT_TYPE_ОСМОТР_ОБЪЕКТА, // 14
    ];

    // набор типов проектов для робота, который формирует PDF из проектов
    // запускается /services/mailing-pdfs
    const PROJECT_TYPES_PDF = [
        ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА,
        ProjectsTypes::PROJECT_TYPE_ВЫВОЗ,
        ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА,
        ProjectsTypes::PROJECT_TYPE_САМОПРИВОЗ,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'responsible_by_project_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_type_id', 'receivers'], 'required'],
            [['project_type_id'], 'integer'],
            [['receivers'], 'string'],
            [['project_type_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_type_id' => 'Тип проекта', // id типа проекта
            'project_type_name' => 'Тип проекта', // наименование типа проекта
            'receivers' => 'Получатели', // по одному на строку
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->project_type_id != null) {
                $types = ProjectsTypes::arrayMapForSelect2(DirectMSSQLQueries::PROJECTS_TYPES_FOR_RESPONSIBLE);
                $this->project_type_name = $types[$this->project_type_id];
            }

            return true;
        }
        return false;
    }
}
