<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "packing_types".
 *
 * @property integer $id
 * @property string $name
 *
 * @property TransportRequestsWaste[] $transportRequestsWastes
 */
class PackingTypes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'packing_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getTransportRequestsWastes()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку видов упаковки и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $attachIdToName bool признак необходимости присоединения идентификатора к наименованию
     * @return array
     */
    public static function arrayMapForSelect2($attachIdToName=null)
    {
        $result = self::find()->orderBy('name,id')->all();
        if (isset($attachIdToName) && $attachIdToName === true)
            return ArrayHelper::map($result, 'id', function($item) {
                return $item['name'] . ' (ID ' . $item['id'] . ')';
            });
        else
            return ArrayHelper::map($result, 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportRequestsWastes()
    {
        return $this->hasMany(TransportRequestsWaste::className(), ['packing_id' => 'id']);
    }
}
