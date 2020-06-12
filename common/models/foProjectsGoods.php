<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_TOVAR_PROJECT".
 *
 * @property int $ID
 * @property int $ID_TOVAR
 * @property int $ID_LIST_PROJECT_COMPANY
 * @property string $DISCRIPTION_TOVAT
 * @property string $CODE
 * @property double $PRICE_TOVAR
 * @property string $ED_IZM_TOVAR
 * @property double $SS_PRICE_TOVAR
 * @property string $PRIM_TOVAR
 * @property double $KOLVO
 * @property double $DISCOUNT
 * @property string $DISCOUNT_NAME
 * @property int $UNITID
 * @property string $UNIQUE_CODE
 * @property string $NDS
 */
class foProjectsGoods extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_TOVAR_PROJECT';
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
            [['ID_TOVAR', 'ID_LIST_PROJECT_COMPANY', 'UNITID'], 'integer'],
            [['PRICE_TOVAR', 'SS_PRICE_TOVAR', 'KOLVO', 'DISCOUNT', 'NDS'], 'number'],
            [['DISCRIPTION_TOVAT'], 'string', 'max' => 400],
            [['CODE', 'ED_IZM_TOVAR', 'UNIQUE_CODE'], 'string', 'max' => 100],
            [['PRIM_TOVAR'], 'string', 'max' => 1000],
            [['DISCOUNT_NAME'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ID_TOVAR' => 'Id Tovar',
            'ID_LIST_PROJECT_COMPANY' => 'Id List Project Company',
            'DISCRIPTION_TOVAT' => 'Discription Tovat',
            'CODE' => 'Code',
            'PRICE_TOVAR' => 'Price Tovar',
            'ED_IZM_TOVAR' => 'Ed Izm Tovar',
            'SS_PRICE_TOVAR' => 'Ss Price Tovar',
            'PRIM_TOVAR' => 'Prim Tovar',
            'KOLVO' => 'Kolvo',
            'DISCOUNT' => 'Discount',
            'DISCOUNT_NAME' => 'Discount Name',
            'UNITID' => 'Unitid',
            'UNIQUE_CODE' => 'Unique Code',
            'NDS' => 'Nds',
        ];
    }
}
