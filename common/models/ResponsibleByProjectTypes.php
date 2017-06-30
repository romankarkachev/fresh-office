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
                $types = DirectMSSQLQueries::arrayMapOfProjectsTypesForSelect2(DirectMSSQLQueries::PROJECTS_TYPES_FOR_RESPONSIBLE);
                $this->project_type_name = $types[$this->project_type_id];
            }

            return true;
        }
        return false;
    }
}
