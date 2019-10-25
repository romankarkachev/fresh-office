<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "LIST_REQUIS_COMPANY".
 *
 * @property int $ID_REQUIS
 * @property int $ID_COMPANY
 * @property string $FULL_NAME
 * @property string $ADRES_YUR
 * @property string $RS
 * @property int $ID_BANK_SPR
 * @property string $BANK_NAME
 * @property string $KS
 * @property string $BIK
 * @property string $DIR_NAME
 * @property string $DIR_STATUS
 * @property string $REQUIS_PRIM
 * @property string $INN
 * @property string $KPP
 * @property string $OKPO
 * @property string $GL_BUH
 * @property string $ADRES_FACT
 * @property string $DIR_NAME_1
 * @property string $DIR_NAME_2
 * @property string $DIR_NAME_3
 * @property string $DIR_NAME_4
 * @property string $KR_NAME
 * @property string $KONT_TEL
 * @property string $DIR_STATUS_1
 * @property string $DIR_STATUS_2
 * @property string $EMAIL
 * @property string $FAX
 * @property string $DEYSTV_CLIENT
 * @property string $DEYSTV_CLIENT1
 * @property string $INN_BANKA
 * @property string $ADRES_BANK
 * @property string $GOROD_BANKA
 * @property string $OGRN_CLIENT
 * @property string $DATA_KEM_OGRN
 * @property string $OKONH
 * @property string $KONT_LISO_COMPANI
 * @property string $USE_DEFAULT
 * @property string $OKATO
 * @property string $OKTMO
 * @property string $OKOGU
 * @property string $OKFS
 * @property string $OKPF
 * @property string $OKVED
 * @property string $POCHT_ADR
 */
class foCompanyDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LIST_REQUIS_COMPANY';
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
            [['ID_COMPANY'], 'required'],
            [['ID_COMPANY', 'ID_BANK_SPR'], 'integer'],
            [['FULL_NAME', 'ADRES_YUR', 'RS', 'BANK_NAME', 'KS', 'BIK', 'DIR_NAME', 'DIR_STATUS', 'REQUIS_PRIM', 'INN', 'KPP', 'OKPO', 'GL_BUH', 'ADRES_FACT', 'DIR_NAME_1', 'DIR_NAME_2', 'DIR_NAME_3', 'DIR_NAME_4', 'KR_NAME', 'KONT_TEL', 'DIR_STATUS_1', 'DIR_STATUS_2', 'EMAIL', 'FAX', 'DEYSTV_CLIENT', 'DEYSTV_CLIENT1', 'INN_BANKA', 'ADRES_BANK', 'GOROD_BANKA', 'OGRN_CLIENT', 'DATA_KEM_OGRN', 'OKONH', 'KONT_LISO_COMPANI', 'USE_DEFAULT', 'OKATO', 'OKTMO', 'OKOGU', 'OKFS', 'OKPF', 'OKVED', 'POCHT_ADR'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_REQUIS' => 'Id  Requis',
            'ID_COMPANY' => 'Id  Company',
            'FULL_NAME' => 'Full  Name',
            'ADRES_YUR' => 'Adres  Yur',
            'RS' => 'Rs',
            'ID_BANK_SPR' => 'Id  Bank  Spr',
            'BANK_NAME' => 'Bank  Name',
            'KS' => 'Ks',
            'BIK' => 'Bik',
            'DIR_NAME' => 'Dir  Name',
            'DIR_STATUS' => 'Dir  Status',
            'REQUIS_PRIM' => 'Requis  Prim',
            'INN' => 'Inn',
            'KPP' => 'Kpp',
            'OKPO' => 'Okpo',
            'GL_BUH' => 'Gl  Buh',
            'ADRES_FACT' => 'Adres  Fact',
            'DIR_NAME_1' => 'Dir  Name 1',
            'DIR_NAME_2' => 'Dir  Name 2',
            'DIR_NAME_3' => 'Dir  Name 3',
            'DIR_NAME_4' => 'Dir  Name 4',
            'KR_NAME' => 'Kr  Name',
            'KONT_TEL' => 'Kont  Tel',
            'DIR_STATUS_1' => 'Dir  Status 1',
            'DIR_STATUS_2' => 'Dir  Status 2',
            'EMAIL' => 'Email',
            'FAX' => 'Fax',
            'DEYSTV_CLIENT' => 'Deystv  Client',
            'DEYSTV_CLIENT1' => 'Deystv  Client1',
            'INN_BANKA' => 'Inn  Banka',
            'ADRES_BANK' => 'Adres  Bank',
            'GOROD_BANKA' => 'Gorod  Banka',
            'OGRN_CLIENT' => 'Ogrn  Client',
            'DATA_KEM_OGRN' => 'Data  Kem  Ogrn',
            'OKONH' => 'Okonh',
            'KONT_LISO_COMPANI' => 'Kont  Liso  Compani',
            'USE_DEFAULT' => 'Use  Default',
            'OKATO' => 'Okato',
            'OKTMO' => 'Oktmo',
            'OKOGU' => 'Okogu',
            'OKFS' => 'Okfs',
            'OKPF' => 'Okpf',
            'OKVED' => 'Okved',
            'POCHT_ADR' => 'Pocht  Adr',
        ];
    }
}
