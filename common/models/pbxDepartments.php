<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "department".
 *
 * @property integer $id
 * @property string $name
 *
 * @property pbxEmployees[] $employees
 * @property pbxInternalPhoneNumber[] $internalPhoneNumbers
 */
class pbxDepartments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'department';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_asterisk');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
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
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getEmployees()->count() > 0)
            return true;
        elseif ($this->getInternalPhoneNumbers()->count() > 0)
            return true;

        return false;
    }

    /**
     * Делает выборку отделов компании из Мини-АТС и возвращает в виде массива.
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
    public function getEmployees()
    {
        return $this->hasMany(pbxEmployees::className(), ['department_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInternalPhoneNumbers()
    {
        return $this->hasMany(pbxInternalPhoneNumber::className(), ['department_id' => 'id']);
    }
}
