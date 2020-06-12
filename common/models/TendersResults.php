<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tenders_results".
 *
 * @property int $id
 * @property int $tender_id Тендер
 * @property int $placed_at Дата и время размещения реестровой записи
 * @property string $name Наименование победителя
 * @property string $inn ИНН
 * @property string $kpp КПП
 * @property string $ogrn ОГРН(ИП)
 * @property int $fo_ca_id ID контрагента из CRM Fresh Office
 * @property string $price Цена победителя
 *
 * @property Tenders $tender
 */
class TendersResults extends \yii\db\ActiveRecord
{
    /**
     * Идентификаторы элементов страницы
     */
    const DOM_IDS = [
        // форма для интерактивного ввода данных о победителе в карточке тендера
        'FORM_ID' => 'frmTenderResults',
        // кнопка для отправки интерактивной формы
        'BUTTON_ID' => 'btnSubmitTenderResults',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_results';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tender_id', 'name'], 'required'],
            [['tender_id', 'placed_at', 'fo_ca_id'], 'integer'],
            [['price'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['inn'], 'string', 'max' => 12],
            [['kpp'], 'string', 'max' => 9],
            [['ogrn'], 'string', 'max' => 15],
            [['name', 'inn', 'kpp', 'ogrn'], 'trim'],
            [['tender_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tenders::class, 'targetAttribute' => ['tender_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tender_id' => 'Тендер',
            'placed_at' => 'Дата и время размещения реестровой записи',
            'name' => 'Наименование победителя',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН(ИП)',
            'fo_ca_id' => 'ID контрагента из CRM Fresh Office',
            'price' => 'Цена победителя',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['placed_at'],
                ],
                'preserveNonEmptyValues' => true,
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTender()
    {
        return $this->hasOne(Tenders::class, ['id' => 'tender_id']);
    }
}
