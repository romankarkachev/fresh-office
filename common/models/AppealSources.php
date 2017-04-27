<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "appeal_sources".
 *
 * @property integer $id
 * @property string $name
 * @property string $search_field
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
            [['name', 'search_field'], 'string', 'max' => 50],
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
            'search_field' => 'Подстрока для идентификации',
        ];
    }

    /**
     * Делает выборку источников обращения и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(AppealSources::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAppeals()
    {
        return $this->hasMany(Appeals::className(), ['as_id' => 'id']);
    }
}
