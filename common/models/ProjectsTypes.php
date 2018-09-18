<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "projects_types".
 *
 * @property integer $id
 * @property string $name
 *
 * @property CorrespondencePackages[] $correspondencePackages
 */
class ProjectsTypes extends \yii\db\ActiveRecord
{
    /**
     * Типы проектов.
     * Таблица в MS SQL - LIST_SPR_PROJECT.
     */
    const PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА = 3;
    const PROJECT_TYPE_ВЫВОЗ = 4;
    const PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА = 5;
    const PROJECT_TYPE_САМОПРИВОЗ = 6;
    const PROJECT_TYPE_ФОТО_ВИДЕО = 7;
    const PROJECT_TYPE_ВЫЕЗДНЫЕ_РАБОТЫ = 8;
    const PROJECT_TYPE_ПРОИЗВОДСТВО = 10;
    const PROJECT_TYPE_ЭКОЛОГИЯ = 11;
    const PROJECT_TYPE_ДОКУМЕНТЫ = 12;
    const PROJECT_TYPE_ОСМОТР_ОБЪЕКТА = 14;
    const PROJECT_TYPE_ДОКУМЕНТЫ_ПОСТОПЛАТА = 15;

    const НАБОР_ДОПУСТИМЫХ_ТИПОВ_ПРОИЗВОДСТВО = [
        self::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА,
        self::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА,
        self::PROJECT_TYPE_ВЫВОЗ,
        self::PROJECT_TYPE_САМОПРИВОЗ,
    ];

    const НАБОР_ВЫВОЗ_ЗАКАЗЫ = [
        ProjectsTypes::PROJECT_TYPE_ВЫВОЗ,
        ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА,
        ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА,
    ];

    const НАБОР_ВЫВОЗ_ЗАКАЗЫ_ДОКУМЕНТЫ = [
        ProjectsTypes::PROJECT_TYPE_ВЫВОЗ,
        ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА,
        ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА,
        ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ,
        ProjectsTypes::PROJECT_TYPE_ДОКУМЕНТЫ_ПОСТОПЛАТА,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'projects_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
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
     * Делает выборку типов проектов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $setOfIds array массив идентификаторов, только они попадут в выборку
     * @return array
     */
    public static function arrayMapForSelect2($setOfIds = null)
    {
        $query = self::find();
        if ($setOfIds != null) $query->where(['in', 'id', $setOfIds]);
        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorrespondencePackages()
    {
        return $this->hasMany(CorrespondencePackages::className(), ['type_id' => 'id']);
    }
}
