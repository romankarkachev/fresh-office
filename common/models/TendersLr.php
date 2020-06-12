<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tenders_lr".
 *
 * @property int $id
 * @property int $tender_id Тендер
 * @property int $lr_id Причина проигрыша
 *
 * @property TendersLossReasons $lr
 * @property Tenders $tender
 */
class TendersLr extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_lr';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tender_id', 'lr_id'], 'required'],
            [['tender_id', 'lr_id'], 'integer'],
            [['lr_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersLossReasons::class, 'targetAttribute' => ['lr_id' => 'id']],
            [['tender_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tenders::class, 'targetAttribute' => ['tender_id' => 'id']],
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
            'lr_id' => 'Причина проигрыша',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLr()
    {
        return $this->hasOne(TendersLossReasons::class, ['id' => 'lr_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTender()
    {
        return $this->hasOne(Tenders::class, ['id' => 'tender_id']);
    }
}
