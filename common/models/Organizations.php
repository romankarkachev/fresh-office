<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "organizations".
 *
 * @property integer $id
 * @property string $name
 * @property string $name_short
 * @property string $name_full
 *
 * @property LicensesFiles[] $licensesFiles
 */
class Organizations extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'organizations';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'name_short', 'name_full'], 'required'],
            [['name', 'name_short', 'name_full'], 'string', 'max' => 255],
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
            'name_short' => 'Сокращенное наименование',
            'name_full' => 'Полное наименование',
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getLicensesFiles()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку организаций и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicensesFiles()
    {
        return $this->hasMany(LicensesFiles::className(), ['organization_id' => 'id']);
    }
}
