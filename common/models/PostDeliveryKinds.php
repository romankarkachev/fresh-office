<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Способы доставки корреспонденции.
 *
 * @property integer $id
 * @property string $name
 *
 * @property CorrespondencePackages[] $correspondencePackages
 */
class PostDeliveryKinds extends \yii\db\ActiveRecord
{
    /**
     * Способы доставки корреспонденции
     */
    const DELIVERY_KIND_САМОВЫВОЗ = 1;
    const DELIVERY_KIND_КУРЬЕР = 2;
    const DELIVERY_KIND_ПОЧТА_РФ = 3;
    const DELIVERY_KIND_MAJOR_EXPRESS = 4;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post_delivery_kinds';
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
     * Делает выборку способов доставки корреспонденции и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorrespondencePackages()
    {
        return $this->hasMany(CorrespondencePackages::className(), ['pd_id' => 'id']);
    }
}
