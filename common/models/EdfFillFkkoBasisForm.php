<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * @property bool $src источник данных для заполнения
 * @property integer $tr_id запрос на транспорт, на основании которого будет заполняться табличная часть
 * @property integer $lr_id запрос лицензий, который может служить основанием для заполнения табличной части документа
 */
class EdfFillFkkoBasisForm extends Model
{
    /**
     * @var bool 1 - запрос на транспорт, 2 - запрос лицензий
     */
    public $src;

    /**
     * @var integer запрос на транспорт
     */
    public $tr_id;

    /**
     * @var integer запрос лицензий
     */
    public $lr_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['src', 'tr_id', 'lr_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'src' => 'Источник данных',
            'tr_id' => 'Запрос на транспорт',
            'lr_id' => 'Запрос лицензий',
        ];
    }
}
