<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthRule $ruleName
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemChildren0
 * @property AuthItem[] $children
 * @property AuthItem[] $parents
 */
class AuthItem extends \yii\db\ActiveRecord
{
    /**
     * Доступные в системе роли
     */
    const ROLE_ACCOUNTANT = 'accountant';
    const ROLE_ACCOUNTANT_B = 'accountant_b';
    const ROLE_ACCOUNTANT_SALARY = 'accountant_s';
    const ROLE_ASSISTANT = 'assistant';
    const ROLE_CUSTOMER = 'customer';
    const ROLE_DPC_HEAD = 'dpc_head';
    const ROLE_ECOLOGIST = 'ecologist';
    const ROLE_ECOLOGIST_HEAD = 'ecologist_head';
    const ROLE_EDF = 'edf';
    const ROLE_FERRYMAN = 'ferryman';
    const ROLE_FOREIGNDRIVER = 'foreignDriver';
    const ROLE_HEAD_ASSIST = 'head_assist';
    const ROLE_LICENSES_UPLOAD = 'licenses_upload';
    const ROLE_LOGIST = 'logist';
    const ROLE_OPERATOR = 'operator';
    const ROLE_OPERATOR_HEAD = 'operator_head';
    const ROLE_PBX = 'pbx';
    const ROLE_PROD_DEPARTMENT_HEAD = 'prod_department_head';
    const ROLE_PROD_FEEDBACK = 'prod_feedback';
    const ROLE_ROLE_DOCUMENTS = 'role_documents';
    const ROLE_ROOT = 'root';
    const ROLE_SALES_DEPARTMENT_HEAD = 'sales_department_head';
    const ROLE_SALES_DEPARTMENT_MANAGER = 'sales_department_manager';
    const ROLE_TENDERS_MANAGER = 'tenders_manager';

    /**
     * Набор экологических ролей
     */
    const ROLES_SET_ECOLOGISTS = [
        self::ROLE_ECOLOGIST,
        self::ROLE_ECOLOGIST_HEAD,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Делает выборку агрегатных состояний и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->orderBy('description')->all(), 'name', 'description');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'child'])->viaTable('auth_item_child', ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'parent'])->viaTable('auth_item_child', ['child' => 'name']);
    }
}