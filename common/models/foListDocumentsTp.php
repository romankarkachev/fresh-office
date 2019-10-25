<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_TOVAR_DOC".
 *
 * @property integer $ID_TOVAR_DOC
 * @property integer $ID_DOC
 * @property string $TOVAR_DOC
 * @property double $KOL_VO
 * @property double $PRICE
 * @property double $SUMMA
 * @property string $ID_TOVAR_1C
 * @property string $ED_IZM_TOVAR
 * @property double $DISCOUNT
 * @property double $PRICE1_DISCOUNT
 * @property string $DISCOUNT_NAME
 * @property double $PRICE1_NOT_D
 * @property string $NDS
 * @property integer $ID_TOVAR
 * @property integer $UNITID
 * @property string $UNIQUE_CODE
 * @property string $NOTE
 *
 * @property foListDocuments $document
 */
class foListDocumentsTp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'LIST_TOVAR_DOC';
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
            [['ID_DOC', 'ID_TOVAR', 'UNITID'], 'integer'],
            [['TOVAR_DOC', 'ID_TOVAR_1C', 'ED_IZM_TOVAR', 'DISCOUNT_NAME', 'UNIQUE_CODE', 'NOTE'], 'string'],
            [['KOL_VO', 'PRICE', 'SUMMA', 'DISCOUNT', 'PRICE1_DISCOUNT', 'PRICE1_NOT_D', 'NDS'], 'number'],
            [['ID_DOC'], 'exist', 'skipOnError' => true, 'targetClass' => foListDocuments::class, 'targetAttribute' => ['ID_DOC' => 'ID_DOC']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_TOVAR_DOC' => 'Id  Tovar  Doc',
            'ID_DOC' => 'Id  Doc',
            'TOVAR_DOC' => 'Tovar  Doc',
            'KOL_VO' => 'Kol  Vo',
            'PRICE' => 'Price',
            'SUMMA' => 'Summa',
            'ID_TOVAR_1C' => 'Id  Tovar 1 C',
            'ED_IZM_TOVAR' => 'Ed  Izm  Tovar',
            'DISCOUNT' => 'Discount',
            'PRICE1_DISCOUNT' => 'Price1  Discount',
            'DISCOUNT_NAME' => 'Discount  Name',
            'PRICE1_NOT_D' => 'Price1  Not  D',
            'NDS' => 'Ставка НДС',
            'ID_TOVAR' => 'Id  Tovar',
            'UNITID' => 'Unitid',
            'UNIQUE_CODE' => 'Unique  Code',
            'NOTE' => 'Note',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocument()
    {
        return $this->hasOne(foListDocuments::class, ['ID_DOC' => 'ID_DOC']);
    }
}
