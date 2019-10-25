<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tenders_content_types".
 *
 * @property int $id
 * @property string $name Наименование
 *
 * @property TendersFiles[] $tendersFiles
 */
class TendersContentTypes extends \yii\db\ActiveRecord
{
    /**
     * Допустимые значения
     */
    const CONTENT_TYPE_ПОЛЬЗОВАТЕЛЬСКИЕ = 1;
    const CONTENT_TYPE_ДОКУМЕНТАЦИЯ = 2;
    const CONTENT_TYPE_ПРОТОКОЛЫ = 3;
    const CONTENT_TYPE_РАЗЪЯСНЕНИЯ_ДОКУМЕНТАЦИИ = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_content_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
        ];
    }

    /**
     * Делает выборку типов контента файлов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $forSearch bool если признак установлен, в массив будет добавлен элемент, позволяющий отключить отбор
     * @return array
     */
    public static function arrayMapForSelect2($forSearch = false)
    {
        $result = ArrayHelper::map(self::find()->orderBy('name')->all(), 'id', 'name');
        if ($forSearch) {
            $result = ArrayHelper::merge([-1 => 'Все'], $result);
        }
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTendersFiles()
    {
        return $this->hasMany(TendersFiles::className(), ['ct_id' => 'id']);
    }
}
