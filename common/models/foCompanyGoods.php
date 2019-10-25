<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_GOODS_COMPANY".
 *
 * @property int $ID_TOVAR_COMPANY
 * @property int $ID_TOVAR
 * @property int $ID_COMPANY
 * @property string $PRICE
 *
 * @property LISTTOVAR $tOVAR
 * @property COMPANY $cOMPANY
 */
class foCompanyGoods extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_GOODS_COMPANY';
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
            [['ID_TOVAR', 'ID_COMPANY'], 'integer'],
            [['PRICE'], 'number'],
            [['ID_TOVAR'], 'exist', 'skipOnError' => true, 'targetClass' => foGoods::className(), 'targetAttribute' => ['ID_TOVAR' => 'ID_TOVAR']],
            [['ID_COMPANY'], 'exist', 'skipOnError' => true, 'targetClass' => foCompany::className(), 'targetAttribute' => ['ID_COMPANY' => 'ID_COMPANY']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_TOVAR_COMPANY' => 'Id  Tovar  Company',
            'ID_TOVAR' => 'Id  Tovar',
            'ID_COMPANY' => 'Id  Company',
            'PRICE' => 'Price',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTovar()
    {
        return $this->hasOne(foGoods::className(), ['ID_TOVAR' => 'ID_TOVAR']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(foCompany::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }
}
