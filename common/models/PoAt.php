<?php

namespace common\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "po_at".
 *
 * @property int $id
 * @property int $is_active Признак активности
 * @property int $company_id Контрагент
 * @property int $ei_id Статья расходов
 * @property string $amount Сумма
 * @property string $comment Комментарий
 * @property string $properties Свойства
 * @property int $periodicity Число месяца
 *
 * @property string $companyName
 * @property string $eiName
 *
 * @property Companies $company
 * @property PoEi $ei
 */
class PoAt extends \yii\db\ActiveRecord
{
    /**
     * @var array массив свойств и значений свойств статьи расходов
     */
    public $propertiesValues;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po_at';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_active', 'company_id', 'ei_id'], 'integer'],
            ['periodicity', 'integer', 'min' => 1, 'max' => 31],
            [['company_id', 'ei_id'], 'required'],
            [['amount'], 'number'],
            [['comment', 'properties'], 'string'],
            [['propertiesValues'], 'safe'],
            ['is_active', 'default', 'value' => true],
            [['comment', 'properties'], 'default', 'value' => null],
            [['ei_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoEi::class, 'targetAttribute' => ['ei_id' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Companies::class, 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_active' => 'Признак активности',
            'company_id' => 'Контрагент',
            'ei_id' => 'Статья расходов',
            'amount' => 'Сумма',
            'comment' => 'Комментарий',
            'properties' => 'Свойства',
            'periodicity' => 'Число месяца',
            // вычисляемые поля
            'companyName' => 'Контрагент',
            'eiName' => 'Статья расходов',
            'eiRepHtml' => 'Статья расходов',
        ];
    }

    /**
     * Выполняет подготовку поля "Свойства" к размещению в базе данных.
     * @return string
     */
    public function preparePropertiesForDbStoring()
    {
        $result = [];
        foreach ($this->propertiesValues as $property_id => $value_id) {
            $property = PoProperties::findOne($property_id);
            $value = PoValues::findOne($value_id['value_id']);
            $result[] = [
                'property_id' => $property_id,
                'propertyName' => $property->name,
                'value_id' => $value->id,
                'valueName' => $value->name,
            ];
        }

        return Json::encode($result, true);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Companies::class, ['id' => 'company_id']);
    }

    /**
     * Возвращает наименование контрагента.
     * @return string
     */
    public function getCompanyName()
    {
        return !empty($this->company) ? $this->company->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEi()
    {
        return $this->hasOne(PoEi::class, ['id' => 'ei_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEiGroup()
    {
        return $this->hasOne(PoEig::class, ['id' => 'group_id'])->via('ei');
    }

    /**
     * Возвращает наименование статьи расходов.
     * @return string
     */
    public function getEiName()
    {
        return !empty($this->ei) ? $this->ei->name : '';
    }

    /**
     * Возвращает представление статьи расходов в формате html.
     * @return string
     */
    public function getEiRepHtml()
    {
        return !empty($this->ei) ? $this->eiGroup->name . ' &rarr; ' . $this->ei->name : '';
    }
}
