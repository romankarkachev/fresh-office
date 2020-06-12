<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_CONTACT_MAN".
 *
 * @property integer $ID_CONTACT_MAN
 * @property integer $ID_COMPANY
 * @property string $CONTACT_MAN_NAME
 * @property string $DISCRIPTION_CONTACT_MAN
 * @property string $DISCRIPTION_CONTACT_MAN2
 * @property string $DATA_HAPY
 * @property string $NAME_PART
 * @property string $FAM_PART
 * @property string $OTCH_PART
 * @property string $RECOM_TIME_CONTACT
 * @property integer $ID_LIST_STATUS_CONTACT_MAN
 * @property integer $TRASH
 * @property string $MANAGER_TRASH
 * @property string $DATE_TRASH
 * @property string $SEX
 * @property string $STATUS_CONTACT_MAN_DATE_CHANGED
 * @property string $CABINET_LOGIN
 * @property string $CABINET_PASS
 * @property integer $IS_DEFAULT
 *
 * @property PHONELOG[] $pHONELOGs
 * @property PHONEUSERLOG[] $pHONEUSERLOGs
 */
class foCompanyContactPersons extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'LIST_CONTACT_MAN';
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
            [['ID_COMPANY'], 'required'],
            [['ID_COMPANY', 'ID_LIST_STATUS_CONTACT_MAN', 'TRASH', 'IS_DEFAULT'], 'integer'],
            [['CONTACT_MAN_NAME', 'DISCRIPTION_CONTACT_MAN', 'DISCRIPTION_CONTACT_MAN2', 'NAME_PART', 'FAM_PART', 'OTCH_PART', 'RECOM_TIME_CONTACT', 'MANAGER_TRASH', 'SEX', 'CABINET_LOGIN', 'CABINET_PASS'], 'string'],
            [['DATA_HAPY', 'DATE_TRASH', 'STATUS_CONTACT_MAN_DATE_CHANGED'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_CONTACT_MAN' => 'Id  Contact  Man',
            'ID_COMPANY' => 'Id  Company',
            'CONTACT_MAN_NAME' => 'Имя',
            'DISCRIPTION_CONTACT_MAN' => 'Discription  Contact  Man',
            'DISCRIPTION_CONTACT_MAN2' => 'Discription  Contact  Man2',
            'DATA_HAPY' => 'Data  Hapy',
            'NAME_PART' => 'Name  Part',
            'FAM_PART' => 'Fam  Part',
            'OTCH_PART' => 'Otch  Part',
            'RECOM_TIME_CONTACT' => 'Recom  Time  Contact',
            'ID_LIST_STATUS_CONTACT_MAN' => 'Id  List  Status  Contact  Man',
            'TRASH' => 'Trash',
            'MANAGER_TRASH' => 'Manager  Trash',
            'DATE_TRASH' => 'Date  Trash',
            'SEX' => 'Sex',
            'STATUS_CONTACT_MAN_DATE_CHANGED' => 'Status  Contact  Man  Date  Changed',
            'CABINET_LOGIN' => 'Cabinet  Login',
            'CABINET_PASS' => 'Cabinet  Pass',
            'IS_DEFAULT' => 'Is  Default',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmail()
    {
        return $this->hasMany(foListEmailClient::class, ['ID_COMPANY' => 'ID_COMPANY', 'ID_CONTACT_MAN' => 'ID_CONTACT_MAN']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhone()
    {
        return $this->hasMany(foListPhones::class, ['ID_COMPANY' => 'ID_COMPANY', 'ID_CONTACT_MAN' => 'ID_CONTACT_MAN']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPHONELOGs()
    {
        return $this->hasMany(PHONELOG::className(), ['CONTACT_ID' => 'ID_CONTACT_MAN']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPHONEUSERLOGs()
    {
        return $this->hasMany(PHONEUSERLOG::className(), ['CONTACT_ID' => 'ID_CONTACT_MAN']);
    }
}
