<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_UNITCODES".
 *
 * @property int $UNITID
 * @property int $UNITParnt
 * @property int $UNITType
 * @property int $UNITCode
 * @property string $UNITDecl
 * @property string $UNITSmbNat
 * @property string $UNITSmbInt
 * @property string $UNITCodNat
 * @property string $UNITCodInt
 * @property int $QTY_SIGN
 */
class foUnits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_UNITCODES';
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
            [['UNITParnt', 'UNITType', 'UNITCode', 'QTY_SIGN'], 'integer'],
            [['UNITDecl', 'UNITSmbNat', 'UNITSmbInt', 'UNITCodNat', 'UNITCodInt'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'UNITID' => 'Unitid',
            'UNITParnt' => 'Unitparnt',
            'UNITType' => 'Unittype',
            'UNITCode' => 'Unitcode',
            'UNITDecl' => 'Unitdecl',
            'UNITSmbNat' => 'Unitsmb Nat',
            'UNITSmbInt' => 'Unitsmb Int',
            'UNITCodNat' => 'Unitcod Nat',
            'UNITCodInt' => 'Unitcod Int',
            'QTY_SIGN' => 'Qty  Sign',
        ];
    }
}
