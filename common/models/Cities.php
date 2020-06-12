<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "city".
 *
 * @property integer $city_id
 * @property integer $country_id
 * @property integer $region_id
 * @property string $name
 */
class Cities extends \yii\db\ActiveRecord
{
    /**
     * Поле для хранения виртуального значения идентификатора города.
     * @var integer
     */
    public $id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'city';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id', 'region_id'], 'integer'],
            [['name'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'city_id' => 'City ID',
            'country_id' => 'Country ID',
            'region_id' => 'Region ID',
            'name' => 'Name',
        ];
    }

    /**
     * Делает выборку статей расходов по группам и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapCitiesOfRussiaByGroupsNamesForSelect2()
    {
        $query = self::find()->select([
            self::tableName() . '.city_id',
            self::tableName() . '.name',
            self::tableName() . '.region_id',
            'groupName' => Regions::tableName() . '.name',
        ])
            ->joinWith(['region'])
            ->where([self::tableName() . '.country_id' => Regions::COUNTRY_RUSSIA_ID])
            ->orderBy(Regions::tableName() . '.name, ' . self::tableName() . '.name');

        $array = $query->asArray()->all();
        if (empty($array)) return [];

        $result = [];
        $current_group = -1;
        $children = [];
        $prev_name = '';
        foreach ($array as $type) {
            if ($type['groupName'] != $current_group && $current_group != -1) {
                $result[$prev_name] = $children;
                $children = [];
            }
            $prev_name = $type['groupName'];
            $children[$type['name']] = $type['name']; // name => name
            $current_group = $type['groupName'];
        }
        if (count($children) > 0) {
            $result[$prev_name] = $children;
        }

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Regions::class, ['region_id' => 'region_id']);
    }
}
