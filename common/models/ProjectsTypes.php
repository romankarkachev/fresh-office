<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "projects_types".
 *
 * @property integer $id
 * @property string $name
 *
 * @property CorrespondencePackages[] $correspondencePackages
 */
class ProjectsTypes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'projects_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorrespondencePackages()
    {
        return $this->hasMany(CorrespondencePackages::className(), ['type_id' => 'id']);
    }
}
