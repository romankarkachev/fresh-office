<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "opfh".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Ferrymen[] $ferrymen
 */
class Opfh extends \yii\db\ActiveRecord
{
    const OPFH_ФИЗЛИЦО = 1;
    const OPFH_ИП = 2;
    const OPFH_ООО = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'opfh';
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
     * Делает выборку организационно-правовых форм и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerrymen()
    {
        return $this->hasMany(Ferrymen::className(), ['opfh_id' => 'id']);
    }
}
