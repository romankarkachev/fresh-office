<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "organizations_bas".
 *
 * @property integer $id
 * @property integer $org_id
 * @property string $bank_an
 * @property string $bank_bik
 * @property string $bank_name
 * @property string $bank_ca
 *
 * @property Organizations $org
 */
class OrganizationsBas extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'organizations_bas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org_id', 'bank_an', 'bank_bik', 'bank_name', 'bank_ca'], 'required'],
            [['org_id'], 'integer'],
            [['bank_an', 'bank_ca'], 'string', 'max' => 25],
            [['bank_bik'], 'string', 'max' => 10],
            [['bank_name'], 'string', 'max' => 255],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::className(), 'targetAttribute' => ['org_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'org_id' => 'Организация',
            'bank_an' => 'Номер р/с',
            'bank_bik' => 'БИК банка',
            'bank_name' => 'Наименование банка',
            'bank_ca' => 'Корр. счет',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrg()
    {
        return $this->hasOne(Organizations::className(), ['id' => 'org_id']);
    }
}
