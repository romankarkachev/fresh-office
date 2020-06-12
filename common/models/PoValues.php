<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "po_values".
 *
 * @property int $id
 * @property int $property_id Свойство
 * @property string $name Наименование
 *
 * @property PoProperties $property
 */
class PoValues extends \yii\db\ActiveRecord
{
    /**
     * Некоторые идентификаторы значений свойств статей расходов
     */
    const VALUE_НДФЛ = 28;
    const VALUE_ПФ = 31;
    const VALUE_ФСС = 27;
    const VALUE_ФСС_НС = 33;
    const VALUE_ФОМС = 32;

    const IMPORT_WAGE_FUND_FIELD_PREFIX = 'prop';

    /**
     * Идентификаторы элементов страницы
     */
    const DOM_IDS = [
        // форма для интерактивного добавления значения свойства
        'PJAX_FORM_ID' => 'frmNewValue',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po_values';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['property_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoProperties::className(), 'targetAttribute' => ['property_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'property_id' => 'Свойство',
            'name' => 'Наименование',
        ];
    }

    /**
     * Делает выборку статей расходов по группам и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapByGroupsForSelect2()
    {
        $array = self::find()->select([
            self::tableName() . '.id',
            self::tableName() . '.name',
            'property_id',
            'groupName' => PoProperties::tableName() . '.name',
        ])->joinWith(['property'])->orderBy(PoProperties::tableName() . '.name, ' . self::tableName() . '.name')->asArray()->all();
        if (empty($array)) return [];

        // обработаем массив, чтобы можно было его использовать в виджете select2 с группами
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
            $children[$type['id']] = $type['name'];
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
    public function getProperty()
    {
        return $this->hasOne(PoProperties::className(), ['id' => 'property_id']);
    }
}
