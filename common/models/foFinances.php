<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_MANYS".
 *
 * @property int $ID_MANY
 * @property int $ID_COMPANY
 * @property int $ID_PRIZNAK_MANY
 * @property int $ID_SUB_PRIZNAK_MANY
 * @property int $ID_VALUTA
 * @property double $SUM_VALUTA
 * @property double $SUM_RUB
 * @property string $DATE_MANY
 * @property int $ID_DOC
 * @property string $DOP_PRIM
 * @property string $DATE_PLAN
 * @property int $ID_NAPR
 * @property int $TRASH
 * @property int $ID_LIST_SPR_STAT_MONY
 * @property int $ID_LIST_SPR_LIST_SCHETS_MONY
 * @property int $ID_LIST_SPR_AGENT_MONY
 * @property string $TIME_MONY
 * @property double $KURS_PEREVOD
 * @property int $ID_MANAGER
 * @property string $ID_CH
 * @property double $KURS_RUR
 * @property string $MANAGER_NAME_ADD
 * @property string $DATE_ADD
 * @property string $MANAGER_NAME_UPD
 * @property string $DATE_UPD
 * @property int $ID_LIST_PROJECT_COMPANY
 * @property int $ID_MANAGER_CREATOR
 * @property string $MANAGER_TRASH
 * @property string $DATE_TRASH
 * @property string $NUMBER_DOC
 * @property string $data_doc
 * @property int $MARKER_ON
 * @property int $ID_MANAGER_MARKER
 * @property string $MARKER_DESCRIPTION
 * @property int $AllowCabinet
 * @property int $ID_AGENT
 * @property int $ID_DEAL
 * @property string $UIDOC1C
 * @property string $CODE_1C
 * @property string $ADD_firma
 * @property int $ID_ADV_CAMPAIGN
 *
 * @property LISTSPRSTATMONY $lISTSPRSTATMONY
 * @property LISTSPRSCHETSMONY $lISTSPRLISTSCHETSMONY
 */
class foFinances extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_MANYS';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_mssql');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ID_COMPANY', 'ID_PRIZNAK_MANY', 'ID_SUB_PRIZNAK_MANY', 'ID_VALUTA', 'ID_DOC', 'ID_NAPR', 'TRASH', 'ID_LIST_SPR_STAT_MONY', 'ID_LIST_SPR_LIST_SCHETS_MONY', 'ID_LIST_SPR_AGENT_MONY', 'ID_MANAGER', 'ID_LIST_PROJECT_COMPANY', 'ID_MANAGER_CREATOR', 'MARKER_ON', 'ID_MANAGER_MARKER', 'AllowCabinet', 'ID_AGENT', 'ID_DEAL', 'ID_ADV_CAMPAIGN'], 'integer'],
            [['SUM_VALUTA', 'SUM_RUB', 'KURS_PEREVOD', 'KURS_RUR'], 'number'],
            [['DATE_MANY', 'DATE_PLAN', 'DATE_ADD', 'DATE_UPD', 'DATE_TRASH', 'data_doc'], 'safe'],
            [['DOP_PRIM', 'MANAGER_NAME_ADD', 'MANAGER_NAME_UPD', 'MANAGER_TRASH', 'CODE_1C', 'ADD_firma'], 'string', 'max' => 255],
            [['TIME_MONY', 'NUMBER_DOC', 'UIDOC1C'], 'string', 'max' => 100],
            [['ID_CH'], 'string', 'max' => 10],
            [['MARKER_DESCRIPTION'], 'string', 'max' => 400],
            //[['ID_LIST_SPR_STAT_MONY'], 'exist', 'skipOnError' => true, 'targetClass' => LISTSPRSTATMONY::className(), 'targetAttribute' => ['ID_LIST_SPR_STAT_MONY' => 'ID_LIST_SPR_STAT_MONY']],
            //[['ID_LIST_SPR_LIST_SCHETS_MONY'], 'exist', 'skipOnError' => true, 'targetClass' => LISTSPRSCHETSMONY::className(), 'targetAttribute' => ['ID_LIST_SPR_LIST_SCHETS_MONY' => 'ID_LIST_SPR_LIST_SCHETS_MONY']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_MANY' => 'Id Many',
            'ID_COMPANY' => 'Id Company',
            'ID_PRIZNAK_MANY' => 'Id Priznak Many',
            'ID_SUB_PRIZNAK_MANY' => 'Id Sub Priznak Many',
            'ID_VALUTA' => 'Id Valuta',
            'SUM_VALUTA' => 'Sum Valuta',
            'SUM_RUB' => 'Sum Rub',
            'DATE_MANY' => 'Date Many',
            'ID_DOC' => 'Id Doc',
            'DOP_PRIM' => 'Dop Prim',
            'DATE_PLAN' => 'Date Plan',
            'ID_NAPR' => 'Id Napr',
            'TRASH' => 'Trash',
            'ID_LIST_SPR_STAT_MONY' => 'Id List Spr Stat Mony',
            'ID_LIST_SPR_LIST_SCHETS_MONY' => 'Id List Spr List Schets Mony',
            'ID_LIST_SPR_AGENT_MONY' => 'Id List Spr Agent Mony',
            'TIME_MONY' => 'Time Mony',
            'KURS_PEREVOD' => 'Kurs Perevod',
            'ID_MANAGER' => 'Id Manager',
            'ID_CH' => 'Id Ch',
            'KURS_RUR' => 'Kurs Rur',
            'MANAGER_NAME_ADD' => 'Manager Name Add',
            'DATE_ADD' => 'Date Add',
            'MANAGER_NAME_UPD' => 'Manager Name Upd',
            'DATE_UPD' => 'Date Upd',
            'ID_LIST_PROJECT_COMPANY' => 'Id List Project Company',
            'ID_MANAGER_CREATOR' => 'Id Manager Creator',
            'MANAGER_TRASH' => 'Manager Trash',
            'DATE_TRASH' => 'Date Trash',
            'NUMBER_DOC' => 'Number Doc',
            'data_doc' => 'Data Doc',
            'MARKER_ON' => 'Marker On',
            'ID_MANAGER_MARKER' => 'Id Manager Marker',
            'MARKER_DESCRIPTION' => 'Marker Description',
            'AllowCabinet' => 'Allow Cabinet',
            'ID_AGENT' => 'Id Agent',
            'ID_DEAL' => 'Id Deal',
            'UIDOC1C' => 'Uidoc1 C',
            'CODE_1C' => 'Code 1 C',
            'ADD_firma' => 'Add Firma',
            'ID_ADV_CAMPAIGN' => 'Id Adv Campaign',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTSPRSTATMONY()
    {
        return $this->hasOne(LISTSPRSTATMONY::className(), ['ID_LIST_SPR_STAT_MONY' => 'ID_LIST_SPR_STAT_MONY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTSPRLISTSCHETSMONY()
    {
        return $this->hasOne(LISTSPRSCHETSMONY::className(), ['ID_LIST_SPR_LIST_SCHETS_MONY' => 'ID_LIST_SPR_LIST_SCHETS_MONY']);
    }
}
