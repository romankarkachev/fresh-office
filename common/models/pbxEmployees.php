<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "employee".
 *
 * @property integer $id
 * @property string $name
 * @property integer $department_id
 *
 * @property pbxDepartments $department
 * @property InternalPhoneNumber[] $internalPhoneNumbers
 */
class pbxEmployees extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'employee';
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
            [['department_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['department_id'], 'exist', 'skipOnError' => true, 'targetClass' => pbxDepartments::className(), 'targetAttribute' => ['department_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ФИО сотрудника',
            'department_id' => 'Отдел',
            // вычисляемые поля
            'departmentName' => 'Отдел',
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getInternalPhoneNumbers()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку операторов мини-АТС и возвращает в виде массива.
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
    public function getDepartment()
    {
        return $this->hasOne(pbxDepartments::className(), ['id' => 'department_id']);
    }

    /**
     * Возвращает наименование отдела.
     * @return string
     */
    public function getDepartmentName()
    {
        return !empty($this->department) ? $this->department->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInternalPhoneNumbers()
    {
        return $this->hasMany(pbxInternalPhoneNumber::className(), ['employee_id' => 'id']);
    }
}
