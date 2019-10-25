<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_TOVAR".
 *
 * @property int $ID_TOVAR
 * @property string $DISCRIPTION_TOVAT наименование товара
 * @property string $CODE
 * @property double $PRICE_TOVAR цена
 * @property double $OST_TOVAR
 * @property int $ID_LIST_TOVAR_GROUPS
 * @property string $ED_IZM_TOVAR
 * @property int $RAZDEL
 * @property double $SS_PRICE_TOVAR
 * @property string $PRIM_TOVAR
 * @property string $CODE_1C
 * @property string $DATE_ACTUAL
 * @property string $PART_NUMBER
 * @property string $NDS Ставка НДС
 * @property int $CURRENCYID
 * @property string $EXT_CODE
 * @property string $COMMENT
 * @property resource $PICTURE
 * @property int $ID_BARCODE_TYPE
 * @property string $BARCODE
 * @property string $WEIGHT
 * @property int $ID_MEASURE_WEIGHT
 * @property string $VOLUME
 * @property int $ID_MEASURE_VOLUME
 * @property int $IS_SALE_EMBARGO
 * @property string $MIN_QNT
 * @property int $ID_GOOD_TYPE
 * @property int $UNITID единица измерения
 * @property string $MIN_PRICE
 * @property int $ID_MANUFACTURER
 * @property string $ADD_klass_opasnosti
 * @property string $ADD_sposob
 * @property string $ADD_stoim_kol
 * @property string $UNIQUE_CODE
 * @property int $TRASH
 * @property string $MANAGER_TRASH
 * @property string $DATE_TRASH
 * @property string $IMG_URL
 * @property string $IMG_URL_110
 * @property string $IMG_URL_24
 * @property string $IMG_URL_PREVIEW
 * @property int $IMG_PREVIEW_WIDTH
 * @property int $IMG_PREVIEW_HEIGHT
 * @property string $ADD_KOD1C_eko_korp
 * @property string $ADD_KOD1C_new
 * @property string $ADD_KOD1C_general2
 * @property string $ADD_KOD1C_logistika
 * @property string $ADD_KOD1C_cuop
 * @property string $ADD_KOD1C_nok
 * @property string $ADD_KOD1C_tmp
 * @property string $ADD_KOD1C_sex
 *
 * @property LISTGOODSCOMPANY[] $lISTGOODSCOMPANies
 * @property LISTSTOCKDOCTOVAR[] $lISTSTOCKDOCTOVARs
 * @property LISTTOVARCOMPOSITION[] $lISTTOVARCOMPOSITIONs
 * @property LISTTOVARSUPPPLIER[] $lISTTOVARSUPPPLIERs
 */
class foGoods extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_TOVAR';
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
            [['DISCRIPTION_TOVAT'], 'required'],
            [['DISCRIPTION_TOVAT', 'CODE', 'ED_IZM_TOVAR', 'PRIM_TOVAR', 'CODE_1C', 'PART_NUMBER', 'EXT_CODE', 'COMMENT', 'PICTURE', 'BARCODE', 'ADD_klass_opasnosti', 'ADD_sposob', 'ADD_stoim_kol', 'UNIQUE_CODE', 'MANAGER_TRASH', 'IMG_URL', 'IMG_URL_110', 'IMG_URL_24', 'IMG_URL_PREVIEW', 'ADD_KOD1C_eko_korp', 'ADD_KOD1C_new', 'ADD_KOD1C_general2', 'ADD_KOD1C_logistika', 'ADD_KOD1C_cuop', 'ADD_KOD1C_nok', 'ADD_KOD1C_tmp', 'ADD_KOD1C_sex'], 'string'],
            [['PRICE_TOVAR', 'OST_TOVAR', 'SS_PRICE_TOVAR', 'NDS', 'WEIGHT', 'VOLUME', 'MIN_QNT', 'MIN_PRICE'], 'number'],
            [['ID_LIST_TOVAR_GROUPS', 'RAZDEL', 'CURRENCYID', 'ID_BARCODE_TYPE', 'ID_MEASURE_WEIGHT', 'ID_MEASURE_VOLUME', 'IS_SALE_EMBARGO', 'ID_GOOD_TYPE', 'UNITID', 'ID_MANUFACTURER', 'TRASH', 'IMG_PREVIEW_WIDTH', 'IMG_PREVIEW_HEIGHT'], 'integer'],
            [['DATE_ACTUAL', 'DATE_TRASH'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_TOVAR' => 'Id  Tovar',
            'DISCRIPTION_TOVAT' => 'Discription  Tovat',
            'CODE' => 'Code',
            'PRICE_TOVAR' => 'Price  Tovar',
            'OST_TOVAR' => 'Ost  Tovar',
            'ID_LIST_TOVAR_GROUPS' => 'Id  List  Tovar  Groups',
            'ED_IZM_TOVAR' => 'Ed  Izm  Tovar',
            'RAZDEL' => 'Razdel',
            'SS_PRICE_TOVAR' => 'Ss  Price  Tovar',
            'PRIM_TOVAR' => 'Prim  Tovar',
            'CODE_1C' => 'Code 1 C',
            'DATE_ACTUAL' => 'Date  Actual',
            'PART_NUMBER' => 'Part  Number',
            'NDS' => 'Ставка НДС',
            'CURRENCYID' => 'Currencyid',
            'EXT_CODE' => 'Ext  Code',
            'COMMENT' => 'Comment',
            'PICTURE' => 'Picture',
            'ID_BARCODE_TYPE' => 'Id  Barcode  Type',
            'BARCODE' => 'Barcode',
            'WEIGHT' => 'Weight',
            'ID_MEASURE_WEIGHT' => 'Id  Measure  Weight',
            'VOLUME' => 'Volume',
            'ID_MEASURE_VOLUME' => 'Id  Measure  Volume',
            'IS_SALE_EMBARGO' => 'Is  Sale  Embargo',
            'MIN_QNT' => 'Min  Qnt',
            'ID_GOOD_TYPE' => 'Id  Good  Type',
            'UNITID' => 'Unitid',
            'MIN_PRICE' => 'Min  Price',
            'ID_MANUFACTURER' => 'Id  Manufacturer',
            'ADD_klass_opasnosti' => 'Add Klass Opasnosti',
            'ADD_sposob' => 'Add Sposob',
            'ADD_stoim_kol' => 'Add Stoim Kol',
            'UNIQUE_CODE' => 'Unique  Code',
            'TRASH' => 'Trash',
            'MANAGER_TRASH' => 'Manager  Trash',
            'DATE_TRASH' => 'Date  Trash',
            'IMG_URL' => 'Img  Url',
            'IMG_URL_110' => 'Img  Url 110',
            'IMG_URL_24' => 'Img  Url 24',
            'IMG_URL_PREVIEW' => 'Img  Url  Preview',
            'IMG_PREVIEW_WIDTH' => 'Img  Preview  Width',
            'IMG_PREVIEW_HEIGHT' => 'Img  Preview  Height',
            'ADD_KOD1C_eko_korp' => 'Add  Kod1 C Eko Korp',
            'ADD_KOD1C_new' => 'Add  Kod1 C New',
            'ADD_KOD1C_general2' => 'Add  Kod1 C General2',
            'ADD_KOD1C_logistika' => 'Add  Kod1 C Logistika',
            'ADD_KOD1C_cuop' => 'Add  Kod1 C Cuop',
            'ADD_KOD1C_nok' => 'Add  Kod1 C Nok',
            'ADD_KOD1C_tmp' => 'Add  Kod1 C Tmp',
            'ADD_KOD1C_sex' => 'Add  Kod1 C Sex',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTGOODSCOMPANies()
    {
        return $this->hasMany(LISTGOODSCOMPANY::className(), ['ID_TOVAR' => 'ID_TOVAR']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTSTOCKDOCTOVARs()
    {
        return $this->hasMany(LISTSTOCKDOCTOVAR::className(), ['ID_TOVAR' => 'ID_TOVAR']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTTOVARCOMPOSITIONs()
    {
        return $this->hasMany(LISTTOVARCOMPOSITION::className(), ['ID_TOVAR' => 'ID_TOVAR']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTTOVARSUPPPLIERs()
    {
        return $this->hasMany(LISTTOVARSUPPPLIER::className(), ['ID_TOVAR' => 'ID_TOVAR']);
    }
}
