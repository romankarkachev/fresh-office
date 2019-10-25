<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "companies_bank_accounts".
 *
 * @property int $id
 * @property int $company_id Контрагент
 * @property string $bank_an Номер р/с
 * @property string $bank_bik БИК банка
 * @property string $bank_name Наименование банка
 * @property string $bank_ca Корр. счет
 *
 * @property Companies $company
 */
class CompaniesBankAccounts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'companies_bank_accounts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'bank_an', 'bank_bik', 'bank_name', 'bank_ca'], 'required'],
            [['company_id'], 'integer'],
            [['bank_an', 'bank_ca'], 'string', 'max' => 25],
            [['bank_bik'], 'string', 'max' => 10],
            [['bank_name'], 'string', 'max' => 255],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Companies::className(), 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Контрагент',
            'bank_an' => 'Номер р/с',
            'bank_bik' => 'БИК банка',
            'bank_name' => 'Наименование банка',
            'bank_ca' => 'Корр. счет',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Companies::className(), ['id' => 'company_id']);
    }
}
