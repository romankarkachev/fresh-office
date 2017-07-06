<?php

namespace common\models;

use Yii;
use common\behaviors\IndexFieldBehavior;

/**
 * This is the model class for table "transport".
 *
 * @property integer $id
 * @property integer $ferryman_id
 * @property integer $tt_id
 * @property integer $brand_id
 * @property string $vin
 * @property string $vin_index
 * @property string $rn
 * @property string $rn_index
 * @property string $trailer_rn
 * @property string $comment
 *
 * @property string $brandName
 * @property string $ttName
 * @property integer $inspCount
 *
 * @property TransportBrands $brand
 * @property Ferrymen $ferryman
 * @property TransportTypes $tt
 * @property TransportInspections[] $transportInspections
 */
class Transport extends \yii\db\ActiveRecord
{
    /**
     * Количество техосмотров, для вложенного подзапроса.
     * Виртуальное поле.
     * @var integer
     */
    public $inspCount;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id'], 'required'],
            [['ferryman_id', 'tt_id', 'brand_id'], 'integer'],
            [['comment'], 'string'],
            [['vin', 'vin_index'], 'string', 'max' => 50],
            [['rn', 'rn_index', 'trailer_rn'], 'string', 'max' => 30],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportBrands::className(), 'targetAttribute' => ['brand_id' => 'id']],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
            [['tt_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportTypes::className(), 'targetAttribute' => ['tt_id' => 'id']],
            // собственные правила валидации
            ['vin', 'validateVin'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ferryman_id' => 'Перевозчик',
            'tt_id' => 'Тип',
            'brand_id' => 'Марка',
            'vin' => 'VIN',
            'rn' => 'Госномер',
            'trailer_rn' => 'Прицеп',
            'comment' => 'Примечание',
            // вычисляемые поля
            'ferrymanName' => 'Перевозчик',
            'ttName' => 'Тип',
            'brandName' => 'Марка',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'indexVinField' => [
                'class' => 'common\behaviors\IndexFieldBehavior',
                'in_attribute' => 'vin',
                'out_attribute' => 'vin_index',
            ],
            'indexRnField' => [
                'class' => 'common\behaviors\IndexFieldBehavior',
                'in_attribute' => 'rn',
                'out_attribute' => 'rn_index',
            ]
        ];
    }

    /**
     * Собственное правило валидации для VIN-номера транспортного средства.
     */
    public function validateVin()
    {
        $query = self::find()->where(['vin_index' => IndexFieldBehavior::processValue($this->vin)]);
        if ($this->id != null) $query->andWhere(['not in', 'id', $this->id]);
        if ($query->count() > 0)
            $this->addError('vin', 'Автомобиль с таким VIN уже существует.');
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением документа

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = TransportFiles::find()->where(['transport_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            // удаляем техсмотры
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $tis = TransportInspections::find()->where(['transport_id' => $this->id])->all();
            foreach ($tis as $ti) $ti->delete();

            return true;
        }

        return false;
    }

    /**
     * Возвращает представление транспортного средства для вывода на экране.
     * @return string
     */
    public function getRepresentation()
    {
        $result = $this->ttName . ' ' . $this->brandName;
        $result = trim($result);
        $result .= ' г/н ' . $this->rn;
        $result = trim($result);

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::className(), ['id' => 'ferryman_id']);
    }

    /**
     * Возвращает наименование перевозчка.
     * @return string
     */
    public function getFerrymanName()
    {
        return $this->ferryman != null ? $this->ferryman->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTt()
    {
        return $this->hasOne(TransportTypes::className(), ['id' => 'tt_id']);
    }

    /**
     * Возвращает наименование типа транспортного средства.
     * @return string
     */
    public function getTtName()
    {
        return $this->tt != null ? $this->tt->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(TransportBrands::className(), ['id' => 'brand_id']);
    }

    /**
     * Возвращает наименование марки автомобиля.
     * @return string
     */
    public function getBrandName()
    {
        return $this->brand != null ? $this->brand->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportInspections()
    {
        return $this->hasMany(TransportInspections::className(), ['transport_id' => 'id']);
    }
}
