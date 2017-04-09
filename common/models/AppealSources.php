<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "appeal_sources".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Appeals[] $appeals
 */
class AppealSources extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'appeal_sources';
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
    public function getAppeals()
    {
        return $this->hasMany(Appeals::className(), ['as_id' => 'id']);
    }
}
