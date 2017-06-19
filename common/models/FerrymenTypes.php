<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ferrymen_types".
 *
 * @property integer $id
 * @property string $name
 */
class FerrymenTypes extends \yii\db\ActiveRecord
{
    const FERRYMAN_TYPE_РАЗОВЫЙ = 1;
    const FERRYMAN_TYPE_ПЕРИОДИЧЕСКИЙ = 2;
    const FERRYMAN_TYPE_ПОСТОЯННЫЙ = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ferrymen_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 30],
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
     * Делает выборку типов перевозчиков и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}
