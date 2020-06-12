<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * @property bool $src источник данных для заполнения
 * @property integer $tr_id запрос на транспорт, на основании которого будет заполняться табличная часть
 * @property integer $lr_id запрос лицензий, который может служить основанием для заполнения табличной части документа
 */
class EdfFillFkkoBasisForm extends Model
{
    /**
     * Возможные значения для поля src
     */
    const SRC_TR = 1;
    const SRC_LR = 2;
    const SRC_EXCEL = 3;

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
     * @var \yii\web\UploadedFile
     */
    public $importFile;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['src', 'tr_id', 'lr_id'], 'integer'],
            [['importFile'], 'file', 'skipOnEmpty' => false, 'extensions' => ['xls', 'xlsx'], 'checkExtensionByMimeType' => false],
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
            'importFile' => 'Файл Excel',
        ];
    }


    /**
     * Возвращает массив с идентификаторами статусов по группам.
     * @return array
     */
    public static function fetchSources()
    {
        return [
            [
                'id' => self::SRC_TR,
                'name' => 'Запрос на транспорт',
                'hint' => 'Импортировать позиции из запроса на транспорт по текущему контрагенту',
            ],
            [
                'id' => self::SRC_LR,
                'name' => 'Запрос лицензий',
                'hint' => 'Импортировать позиции из запроса лицензий по текущему контрагенту',
            ],
            [
                'id' => self::SRC_EXCEL,
                'name' => 'Excel',
                'hint' => 'Импорт из файла Excel',
            ],
        ];
    }

    /**
     * Делает выборку значений для поля "Источник данных" и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfSourcesForSelect2()
    {
        return ArrayHelper::map(self::fetchSources(), 'id', 'name');
    }
}
