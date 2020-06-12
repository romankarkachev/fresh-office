<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_CURATOR_COMPANY".
 *
 * @property int $ID_LIST_CURATOR_COMPANY
 * @property int $ID_COMPANY
 * @property int $ID_MANAGER
 * @property string $DATE_CREATE
 * @property string $PRIM_CURATOR
 *
 * @property string $companyName
 *
 * @property foCompany $company
 */
class foCompanyCurator extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_CURATOR_COMPANY';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_mssql');
    }

    /**
     * {@inheritDoc}
     */
    public static function primaryKey()
    {
        return ['ID_COMPANY'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ID_COMPANY', 'ID_MANAGER'], 'integer'],
            [['DATE_CREATE'], 'safe'],
            [['PRIM_CURATOR'], 'string', 'max' => 255],
            [['ID_COMPANY'], 'exist', 'skipOnError' => true, 'targetClass' => foCompany::class, 'targetAttribute' => ['ID_COMPANY' => 'ID_COMPANY']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_LIST_CURATOR_COMPANY' => 'Id List Curator Company',
            'ID_COMPANY' => 'ID',
            'ID_MANAGER' => 'Id Manager',
            'DATE_CREATE' => 'Date Create',
            'PRIM_CURATOR' => 'Prim Curator',
            // вычисляемые поля
            'companyName' => 'Компания',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(foCompany::class, ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * Возвращает наименование компании.
     * @return string
     */
    public function getCompanyName()
    {
        return !empty($this->company) ? $this->company->COMPANY_NAME : '';
    }
}
