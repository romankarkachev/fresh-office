<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "production_sites".
 *
 * @property int $id
 * @property string $name Наименование
 * @property int $fo_ca_id Контрагент в CRM
 *
 * @property string $companyManager
 *
 * @property foCompany $company
 * @property ProductionShipment[] $productionShipments
 */
class ProductionSites extends \yii\db\ActiveRecord
{
    const LABEL_ROOT = 'Производственные площадки';

    const URL_ROOT = 'production-sites';
    const URL_ROOT_ROUTE = '/' . self::URL_ROOT;
    const URL_ROOT_ROUTE_AS_ARRAY = [self::URL_ROOT_ROUTE];

    const URL_UPDATE = 'update';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'production_sites';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['fo_ca_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
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
            'fo_ca_id' => 'Контрагент в CRM',
        ];
    }

    /**
     * Делает выборку производственных площадок и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(foCompany::class, ['ID_COMPANY' => 'fo_ca_id']);
    }

    /**
     * @return yii\db\ActiveQuery
     */
    public function getCompanyManager()
    {
        return $this->hasOne(foManagers::class, ['ID_MANAGER' => 'ID_MANAGER'])->via('company');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductionShipments()
    {
        return $this->hasMany(ProductionShipment::class, ['site_id' => 'id']);
    }
}
