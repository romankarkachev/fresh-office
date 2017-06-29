<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "transport_inspections".
 *
 * @property integer $id
 * @property integer $transport_id
 * @property string $inspected_at
 * @property string $place
 * @property string $responsible
 * @property integer $tc_id
 * @property string $comment
 *
 * @property TechnicalConditions $tc
 * @property Transport $transport
 */
class TransportInspections extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport_inspections';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transport_id', 'inspected_at'], 'required'],
            [['transport_id', 'tc_id'], 'integer'],
            [['inspected_at'], 'safe'],
            [['comment'], 'string'],
            [['place', 'responsible'], 'string', 'max' => 50],
            [['tc_id'], 'exist', 'skipOnError' => true, 'targetClass' => TechnicalConditions::className(), 'targetAttribute' => ['tc_id' => 'id']],
            [['transport_id'], 'exist', 'skipOnError' => true, 'targetClass' => Transport::className(), 'targetAttribute' => ['transport_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transport_id' => 'Транспорт',
            'inspected_at' => 'Дата осмотра',
            'place' => 'Место проведения',
            'responsible' => 'Ответственный',
            'tc_id' => 'Техническое состояние',
            'comment' => 'Комментарий',
            // вычисляемые поля
            'tcName' => 'Техническое состояние',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransport()
    {
        return $this->hasOne(Transport::className(), ['id' => 'transport_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTc()
    {
        return $this->hasOne(TechnicalConditions::className(), ['id' => 'tc_id']);
    }

    /**
     * Возвращает наименование технического состояния транспортного средства.
     * @return string
     */
    public function getTcName()
    {
        return $this->tc != null ? $this->tc->name : '';
    }
}
