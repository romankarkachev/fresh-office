<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "internal_phone_number".
 *
 * @property integer $id
 * @property string $phone_number
 * @property integer $department_id
 * @property integer $employee_id
 *
 * @property string $employeeName
 * @property string $departmentName
 *
 * @property pbxEmployees $employee
 * @property pbxDepartments $department
 */
class pbxInternalPhoneNumber extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'internal_phone_number';
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
            [['phone_number', 'employee_id'], 'required'],
            [['department_id', 'employee_id'], 'integer'],
            [['phone_number'], 'string', 'max' => 20],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => pbxEmployees::className(), 'targetAttribute' => ['employee_id' => 'id']],
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
            'phone_number' => 'Номер телефона',
            'department_id' => 'Отдел',
            'employee_id' => 'Сотрудник',
            // вычисляемые поля
            'departmentName' => 'Отдел',
            'employeeName' => 'Сотрудник',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(pbxEmployees::className(), ['id' => 'employee_id']);
    }

    /**
     * Возвращает имя сотрудника
     * @return string
     */
    public function getEmployeeName()
    {
        return !empty($this->employee) ? $this->employee->name : '';
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
}
