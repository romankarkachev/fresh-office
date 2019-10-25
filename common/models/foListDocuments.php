<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_DOCUMENTS".
 *
 * @property integer $ID_DOC
 * @property integer $ID_COMPANY
 * @property integer $ID_TIP_DOC
 * @property integer $ID_PRIZNZK_DOC
 * @property string $NUMBER_DOC
 * @property string $DATA_DOC
 * @property string $PRIM_DOC
 * @property integer $ID_SPR_REQUIS
 * @property integer $ID_VALUTA
 * @property integer $ID_CONTACT_MAN
 * @property double $SUMMA
 * @property integer $ID_PRIZNAK_INCLUDE_TOVAR
 * @property integer $ID_REQUIS_COMPANY
 * @property integer $ID_DOC_OSNOV
 * @property integer $ID_LIST_REQUIS_FIZ
 * @property string $SUM_PROPIS
 * @property string $NA_OSNOV_COMPANY
 * @property string $NA_OSNOV_CLIENT
 * @property integer $ID_MANAGER
 * @property integer $TRASH
 * @property string $ID_CH
 * @property integer $ID_MANAGER_EXE
 * @property integer $ID_LIST_PROJECT_COMPANY
 * @property string $MANAGER_TRASH
 * @property string $DATE_TRASH
 * @property string $CODE_DOC_1C
 * @property integer $MARKER_ON
 * @property integer $ID_MANAGER_MARKER
 * @property string $MARKER_DESCRIPTION
 * @property integer $AllowCabinet
 * @property integer $NDS_DIRECTION
 * @property string $DATE_CLOSING
 * @property integer $ID_DEAL
 * @property integer $ID_STOCK
 * @property integer $ID_STOCK_TARGET
 * @property integer $IS_APPLIED
 *
 * @property PRIZNAKDOCUMENTS $iDPRIZNZKDOC
 * @property COMPANY $iDCOMPANY
 * @property TIPDOCUMENTS $iDTIPDOC
 * @property LISTTOVARDOC[] $lISTTOVARDOCs
 */
class foListDocuments extends \yii\db\ActiveRecord
{
    const НАБОР_ТИПОВ_ДОКУМЕНТОВ_ПРИЗНАВАЕМЫХ_СЧЕТАМИ = [
        27,
        28,
        29,
        30,
        31,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'LIST_DOCUMENTS';
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
            [['ID_COMPANY', 'ID_TIP_DOC', 'ID_PRIZNZK_DOC'], 'required'],
            [['ID_COMPANY', 'ID_TIP_DOC', 'ID_PRIZNZK_DOC', 'ID_SPR_REQUIS', 'ID_VALUTA', 'ID_CONTACT_MAN', 'ID_PRIZNAK_INCLUDE_TOVAR', 'ID_REQUIS_COMPANY', 'ID_DOC_OSNOV', 'ID_LIST_REQUIS_FIZ', 'ID_MANAGER', 'TRASH', 'ID_MANAGER_EXE', 'ID_LIST_PROJECT_COMPANY', 'MARKER_ON', 'ID_MANAGER_MARKER', 'AllowCabinet', 'NDS_DIRECTION', 'ID_DEAL', 'ID_STOCK', 'ID_STOCK_TARGET', 'IS_APPLIED'], 'integer'],
            [['NUMBER_DOC', 'PRIM_DOC', 'SUM_PROPIS', 'NA_OSNOV_COMPANY', 'NA_OSNOV_CLIENT', 'ID_CH', 'MANAGER_TRASH', 'CODE_DOC_1C', 'MARKER_DESCRIPTION'], 'string'],
            [['DATA_DOC', 'DATE_TRASH', 'DATE_CLOSING'], 'safe'],
            [['SUMMA'], 'number'],
            [['ID_PRIZNZK_DOC'], 'exist', 'skipOnError' => true, 'targetClass' => PRIZNAKDOCUMENTS::className(), 'targetAttribute' => ['ID_PRIZNZK_DOC' => 'ID_PRIZNAK_DOCUMENT']],
            [['ID_COMPANY'], 'exist', 'skipOnError' => true, 'targetClass' => COMPANY::className(), 'targetAttribute' => ['ID_COMPANY' => 'ID_COMPANY']],
            [['ID_TIP_DOC'], 'exist', 'skipOnError' => true, 'targetClass' => TIPDOCUMENTS::className(), 'targetAttribute' => ['ID_TIP_DOC' => 'ID_TIP_DOCUMENT']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_DOC' => 'Id  Doc',
            'ID_COMPANY' => 'Id  Company',
            'ID_TIP_DOC' => 'Id  Tip  Doc',
            'ID_PRIZNZK_DOC' => 'Id  Priznzk  Doc',
            'NUMBER_DOC' => 'Number  Doc',
            'DATA_DOC' => 'Data  Doc',
            'PRIM_DOC' => 'Prim  Doc',
            'ID_SPR_REQUIS' => 'Id  Spr  Requis',
            'ID_VALUTA' => 'Id  Valuta',
            'ID_CONTACT_MAN' => 'Id  Contact  Man',
            'SUMMA' => 'Summa',
            'ID_PRIZNAK_INCLUDE_TOVAR' => 'Id  Priznak  Include  Tovar',
            'ID_REQUIS_COMPANY' => 'Id  Requis  Company',
            'ID_DOC_OSNOV' => 'Id  Doc  Osnov',
            'ID_LIST_REQUIS_FIZ' => 'Id  List  Requis  Fiz',
            'SUM_PROPIS' => 'Sum  Propis',
            'NA_OSNOV_COMPANY' => 'Na  Osnov  Company',
            'NA_OSNOV_CLIENT' => 'Na  Osnov  Client',
            'ID_MANAGER' => 'Id  Manager',
            'TRASH' => 'Trash',
            'ID_CH' => 'Id  Ch',
            'ID_MANAGER_EXE' => 'Id  Manager  Exe',
            'ID_LIST_PROJECT_COMPANY' => 'Id  List  Project  Company',
            'MANAGER_TRASH' => 'Manager  Trash',
            'DATE_TRASH' => 'Date  Trash',
            'CODE_DOC_1C' => 'Code  Doc 1 C',
            'MARKER_ON' => 'Marker  On',
            'ID_MANAGER_MARKER' => 'Id  Manager  Marker',
            'MARKER_DESCRIPTION' => 'Marker  Description',
            'AllowCabinet' => 'Allow Cabinet',
            'NDS_DIRECTION' => 'Направление расчета. 0 - выдел',
            'DATE_CLOSING' => 'Дата окончания действия докуме',
            'ID_DEAL' => 'Id  Deal',
            'ID_STOCK' => 'Id  Stock',
            'ID_STOCK_TARGET' => 'Id  Stock  Target',
            'IS_APPLIED' => 'Is  Applied',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIDPRIZNZKDOC()
    {
        return $this->hasOne(PRIZNAKDOCUMENTS::className(), ['ID_PRIZNAK_DOCUMENT' => 'ID_PRIZNZK_DOC']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIDCOMPANY()
    {
        return $this->hasOne(COMPANY::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIDTIPDOC()
    {
        return $this->hasOne(TIPDOCUMENTS::className(), ['ID_TIP_DOCUMENT' => 'ID_TIP_DOC']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTTOVARDOCs()
    {
        return $this->hasMany(LISTTOVARDOC::className(), ['ID_DOC' => 'ID_DOC']);
    }
}
