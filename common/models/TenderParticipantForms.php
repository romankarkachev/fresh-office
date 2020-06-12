<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Генерация набора форм по шаблонам. Отдает результат в виде архива на скачивание.
 */
class TenderParticipantForms extends Model
{
    /**
     * @var int идентификатор тендера
     */
    public $tender_id;

    /**
     * @var int идентификатор разновидности
     */
    public $variety_id;

    /**
     * @var array набор формы, приаттаченных к разновидности
     */
    public $items;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['variety_id'], 'required'],
            [['tender_id', 'variety_id'], 'integer'],
            ['items', 'safe'],
            [['tender_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tenders::class, 'targetAttribute' => ['tender_id' => 'id']],
            [['variety_id'], 'exist', 'skipOnError' => true, 'targetClass' => TenderFormsVarieties::class, 'targetAttribute' => ['variety_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tender_id' => 'Тендер',
            'variety_id' => 'Разновидность форм',
            'items' => 'Поля форм',
        ];
    }

    /**
     * @return TenderFormsVarieties
     */
    public function getVariety()
    {
        return TenderFormsVarieties::findOne(['id' => $this->variety_id]);
    }
}
