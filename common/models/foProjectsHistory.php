<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_HISTORY_PROJECT_COMPANY".
 *
 * @property integer $ID_LIST_HISTORY_PROJECT
 * @property string $DATE_CHENCH_PRIZNAK
 * @property string $TIME_CHENCH_PRIZNAK
 * @property integer $ID_PRIZNAK_PROJECT
 * @property integer $ID_MANAGER
 * @property integer $ID_LIST_PROJECT_COMPANY
 * @property string $RUN_NAME_CHANCH
 */
class foProjectsHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CBaseCRM_Fresh_7x.dbo.LIST_HISTORY_PROJECT_COMPANY';
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
            [['DATE_CHENCH_PRIZNAK'], 'safe'],
            [['TIME_CHENCH_PRIZNAK', 'RUN_NAME_CHANCH'], 'string'],
            [['ID_PRIZNAK_PROJECT', 'ID_MANAGER', 'ID_LIST_PROJECT_COMPANY'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_LIST_HISTORY_PROJECT' => 'ID',
            'DATE_CHENCH_PRIZNAK' => 'Дата добавления',
            'TIME_CHENCH_PRIZNAK' => 'Время добавления',
            'ID_PRIZNAK_PROJECT' => 'Новый статус',
            'ID_MANAGER' => 'Ответственный',
            'ID_LIST_PROJECT_COMPANY' => 'Проект',
            'RUN_NAME_CHANCH' => 'Описание',
        ];
    }
}
