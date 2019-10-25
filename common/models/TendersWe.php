<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tenders_we".
 *
 * @property int $id
 * @property int $tender_id Тендер
 * @property int $we_id Оборудование
 *
 * @property WasteEquipment $we
 * @property Tenders $tender
 */
class TendersWe extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_we';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['we_id'], 'required'],
            [['tender_id', 'we_id'], 'integer'],
            [['we_id'], 'exist', 'skipOnError' => true, 'targetClass' => WasteEquipment::className(), 'targetAttribute' => ['we_id' => 'id']],
            [['tender_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tenders::className(), 'targetAttribute' => ['tender_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tender_id' => 'Тендер',
            'we_id' => 'Оборудование',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWe()
    {
        return $this->hasOne(WasteEquipment::className(), ['id' => 'we_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTender()
    {
        return $this->hasOne(Tenders::className(), ['id' => 'tender_id']);
    }
}
