<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_PROPERTIES_PROGECT_COMPANY".
 *
 * @property integer $ID_LIST_PROPERTIES_PROGECT_COMPANY
 * @property integer $ID_LIST_PROJECT_COMPANY
 * @property integer $ID_LIST_PROPERTIES_PROGECT
 * @property string $VALUES_PROPERTIES_PROGECT
 * @property string $MANAGER_NAME_CHANCH_VALUE
 * @property string $MANAGER_NAME_CREATOR
 * @property string $PROPERTIES_PROGECT
 * @property integer $PROPERTY_TYPE
 *
 * @property LISTSPRPROPERTIESPROGECT $iDLISTPROPERTIESPROGECT
 */
class foProjectsParameters extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'LIST_PROPERTIES_PROGECT_COMPANY';
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
            [['ID_LIST_PROJECT_COMPANY', 'ID_LIST_PROPERTIES_PROGECT', 'PROPERTY_TYPE'], 'integer'],
            [['VALUES_PROPERTIES_PROGECT', 'MANAGER_NAME_CHANCH_VALUE', 'MANAGER_NAME_CREATOR', 'PROPERTIES_PROGECT'], 'string'],
            [['ID_LIST_PROPERTIES_PROGECT'], 'exist', 'skipOnError' => true, 'targetClass' => LISTSPRPROPERTIESPROGECT::className(), 'targetAttribute' => ['ID_LIST_PROPERTIES_PROGECT' => 'ID_LIST_PROPERTIES_PROGECT']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_LIST_PROPERTIES_PROGECT_COMPANY' => 'Id  List  Properties  Progect  Company',
            'ID_LIST_PROJECT_COMPANY' => 'Id  List  Project  Company',
            'ID_LIST_PROPERTIES_PROGECT' => 'Id  List  Properties  Progect',
            'VALUES_PROPERTIES_PROGECT' => 'Values  Properties  Progect',
            'MANAGER_NAME_CHANCH_VALUE' => 'Manager  Name  Chanch  Value',
            'MANAGER_NAME_CREATOR' => 'Manager  Name  Creator',
            'PROPERTIES_PROGECT' => 'Properties  Progect',
            'PROPERTY_TYPE' => 'Property  Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIDLISTPROPERTIESPROGECT()
    {
        return $this->hasOne(LISTSPRPROPERTIESPROGECT::className(), ['ID_LIST_PROPERTIES_PROGECT' => 'ID_LIST_PROPERTIES_PROGECT']);
    }
}
