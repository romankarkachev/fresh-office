<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ferrymen_bank_details".
 *
 * @property integer $id
 * @property integer $ferryman_id
 * @property string $name_full
 * @property string $inn
 * @property string $kpp
 * @property string $ogrn
 * @property string $bank_an
 * @property string $bank_bik
 * @property string $bank_name
 * @property string $bank_ca
 * @property string $address_j
 * @property string $address_f
 * @property string $contract_num
 * @property string $contract_date
 * @property string $comment
 *
 * @property Ferrymen $ferryman
 */
class FerrymenBankDetails extends \yii\db\ActiveRecord
{
    /**
     * Виртуальное вычисляемое поле.
     * @var string представление счета в виде: [номер счета] в [банк].
     */
    public $accountRep;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ferrymen_bank_details';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id', 'name_full', 'inn', 'ogrn', 'bank_an', 'bank_ca', 'bank_bik', 'bank_name'], 'required'],
            [['ferryman_id'], 'integer'],
            [['address_j', 'address_f', 'comment'], 'string'],
            [['contract_date'], 'safe'],
            [['comment'], 'string'],
            [['name_full'], 'string', 'max' => 200],
            [['inn'], 'string', 'max' => 12],
            [['kpp'], 'string', 'max' => 9],
            [['ogrn'], 'string', 'max' => 15],
            [['bank_an', 'bank_ca'], 'string', 'max' => 25],
            [['bank_bik'], 'string', 'max' => 10],
            [['bank_name'], 'string', 'max' => 255],
            [['contract_num'], 'string', 'max' => 30],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ferryman_id' => 'Перевозчик',
            'name_full' => 'Наименование организации',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН(ИП)',
            'bank_an' => 'Номер р/с',
            'bank_bik' => 'БИК банка',
            'bank_name' => 'Наименование банка',
            'bank_ca' => 'Корр. счет',
            'address_j' => 'Адрес юридический',
            'address_f' => 'Адрес фактический',
            'contract_num' => 'Номер договора',
            'contract_date' => 'Дата договора',
            'comment' => 'Примечания',
        ];
    }

    /**
     * Делает выборку способов расчетов с перевозчиками и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $ferryman_id integer идентификатор перевозчика
     * @return array
     */
    public static function arrayMapForSelect2($ferryman_id)
    {
        if (!isset($ferryman_id)) return [];

        $query = FerrymenBankDetails::find();
        $query->select(['id', 'accountRep' => 'CONCAT(bank_an, " в ", bank_name)']);
        $query->where(['ferryman_id' => $ferryman_id]);

        return ArrayHelper::map($query->all(), 'id', 'accountRep');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::className(), ['id' => 'ferryman_id']);
    }
}
