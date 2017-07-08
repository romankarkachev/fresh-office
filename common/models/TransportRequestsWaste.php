<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "transport_requests_waste".
 *
 * @property integer $id
 * @property integer $tr_id
 * @property integer $fkko_id
 * @property string $fkko_name
 * @property integer $dc_id
 * @property integer $packing_id
 * @property integer $ags_id
 * @property integer $unit_id
 * @property string $measure
 *
 * @property string $fkkoCode
 * @property string $fkkoName
 * @property string $fkkoRep
 * @property string $dcName
 * @property string $packingName
 * @property string $agsName
 * @property string $unitName
 *
 * @property Units $unit
 * @property AggregateStates $ags
 * @property DangerClasses $dc
 * @property Fkko $fkko
 * @property PackingTypes $packing
 * @property TransportRequests $tr
 */
class TransportRequestsWaste extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport_requests_waste';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tr_id', 'fkko_name', 'unit_id', 'measure'], 'required'],
            [['tr_id', 'fkko_id', 'dc_id', 'packing_id', 'ags_id', 'unit_id'], 'integer'],
            [['measure'], 'number'],
            [['fkko_name'], 'string', 'max' => 255],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => Units::className(), 'targetAttribute' => ['unit_id' => 'id']],
            [['ags_id'], 'exist', 'skipOnError' => true, 'targetClass' => AggregateStates::className(), 'targetAttribute' => ['ags_id' => 'id']],
            [['dc_id'], 'exist', 'skipOnError' => true, 'targetClass' => DangerClasses::className(), 'targetAttribute' => ['dc_id' => 'id']],
            [['fkko_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fkko::className(), 'targetAttribute' => ['fkko_id' => 'id']],
            [['packing_id'], 'exist', 'skipOnError' => true, 'targetClass' => PackingTypes::className(), 'targetAttribute' => ['packing_id' => 'id']],
            [['tr_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportRequests::className(), 'targetAttribute' => ['tr_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tr_id' => 'Запрос на транспорт',
            'fkko_id' => 'Код ФККО',
            'fkko_name' => 'ФККО',
            'dc_id' => 'Класс опасности',
            'packing_id' => 'Тип упаковки',
            'ags_id' => 'Агрегатное состояние',
            'unit_id' => 'Единица измерения',
            'measure' => 'Количество',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTr()
    {
        return $this->hasOne(TransportRequests::className(), ['id' => 'tr_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkko()
    {
        return $this->hasOne(Fkko::className(), ['id' => 'fkko_id']);
    }

    /**
     * Возвращает код ФККО отхода.
     * @return string
     */
    public function getFkkoCode()
    {
        return $this->fkko != null ? $this->fkko->fkko_code : '';
    }

    /**
     * Возвращает наименование отхода.
     * @return string
     */
    public function getFkkoName()
    {
        return $this->fkko != null ? $this->fkko->fkko_name : '';
    }

    /**
     * Возвращает представление отхода.
     * @return string
     */
    public function getFkkoRep()
    {
        return $this->fkko != null ? $this->fkko->fkko_name . ' - ' . $this->fkko->fkko_name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDc()
    {
        return $this->hasOne(DangerClasses::className(), ['id' => 'dc_id']);
    }

    /**
     * Возвращает наименование класса опасности.
     * @return string
     */
    public function getDcName()
    {
        return $this->dc != null ? $this->dc->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPacking()
    {
        return $this->hasOne(PackingTypes::className(), ['id' => 'packing_id']);
    }

    /**
     * Возвращает наименование вида упаковки.
     * @return string
     */
    public function getPackingName()
    {
        return $this->packing != null ? $this->packing->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgs()
    {
        return $this->hasOne(AggregateStates::className(), ['id' => 'ags_id']);
    }

    /**
     * Возвращает наименование агрегатного состояния.
     * @return string
     */
    public function getAgsName()
    {
        return $this->ags != null ? $this->ags->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(Units::className(), ['id' => 'unit_id']);
    }

    /**
     * Возвращает наименование единицы измерения.
     * @return string
     */
    public function getUnitName()
    {
        return $this->unit != null ? $this->unit->name : '';
    }
}
