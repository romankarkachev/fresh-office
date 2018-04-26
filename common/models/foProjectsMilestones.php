<?php

namespace common\models;

use Yii;

/**
 * Этапы проектов.
 *
 * @property integer $ID_LIST_STEP_PROGECT_COMPANY
 * @property integer $ID_LIST_PROJECT_COMPANY
 * @property string $STEPS_PROGECT
 * @property string $DATE_START_STEP
 * @property string $DATE_END_STEP
 * @property integer $ID_MANAGER
 * @property integer $ID_LIST_PRIZNAK_STEP_PROGECT
 * @property string $MANAGER_NAME_CHANCH_VALUE
 * @property string $MANAGER_NAME_CREATOR
 * @property integer $N_PP
 * @property integer $PROCENT
 * @property string $ACTUAL_DATE_FINISH
 * @property string $ACTUAL_DATE_START
 * @property integer $OPTION_SCHLURER
 * @property integer $TYPE_SCHLURER
 * @property integer $MARKER_ON
 * @property integer $ID_MANAGER_MARKER
 * @property string $MARKER_DESCRIPTION
 * @property string $USE_TASK_NPP
 * @property integer $ID_GROUP_COMPANY
 */
class foProjectsMilestones extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'LIST_STEPS_PROGECT_COMPANY';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_mssql');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID_LIST_PROJECT_COMPANY', 'ID_MANAGER', 'ID_LIST_PRIZNAK_STEP_PROGECT', 'N_PP', 'PROCENT', 'OPTION_SCHLURER', 'TYPE_SCHLURER', 'MARKER_ON', 'ID_MANAGER_MARKER', 'ID_GROUP_COMPANY'], 'integer'],
            [['STEPS_PROGECT', 'MANAGER_NAME_CHANCH_VALUE', 'MANAGER_NAME_CREATOR', 'MARKER_DESCRIPTION', 'USE_TASK_NPP'], 'string'],
            [['DATE_START_STEP', 'DATE_END_STEP', 'ACTUAL_DATE_FINISH', 'ACTUAL_DATE_START'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_LIST_STEP_PROGECT_COMPANY' => 'Id  List  Step  Progect  Company',
            'ID_LIST_PROJECT_COMPANY' => 'Id  List  Project  Company',
            'STEPS_PROGECT' => 'Steps  Progect',
            'DATE_START_STEP' => 'Date  Start  Step',
            'DATE_END_STEP' => 'Date  End  Step',
            'ID_MANAGER' => 'Id  Manager',
            'ID_LIST_PRIZNAK_STEP_PROGECT' => 'Id  List  Priznak  Step  Progect',
            'MANAGER_NAME_CHANCH_VALUE' => 'Manager  Name  Chanch  Value',
            'MANAGER_NAME_CREATOR' => 'Manager  Name  Creator',
            'N_PP' => 'N  Pp',
            'PROCENT' => 'Procent',
            'ACTUAL_DATE_FINISH' => 'Actual  Date  Finish',
            'ACTUAL_DATE_START' => 'Actual  Date  Start',
            'OPTION_SCHLURER' => 'Option  Schlurer',
            'TYPE_SCHLURER' => 'Type  Schlurer',
            'MARKER_ON' => 'Marker  On',
            'ID_MANAGER_MARKER' => 'Id  Manager  Marker',
            'MARKER_DESCRIPTION' => 'Marker  Description',
            'USE_TASK_NPP' => 'Use  Task  Npp',
            'ID_GROUP_COMPANY' => 'Id  Group  Company',
        ];
    }
}
