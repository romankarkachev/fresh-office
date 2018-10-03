<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "eco_types_milestones".
 *
 * @property integer $id
 * @property integer $type_id
 * @property integer $milestone_id
 * @property integer $is_file_reqiured
 * @property integer $is_affects_to_cycle_time
 * @property integer $time_to_complete_required
 * @property integer $order_no
 *
 * @property EcoMilestones $milestone
 * @property EcoTypes $type
 */
class EcoTypesMilestones extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eco_types_milestones';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'milestone_id', 'time_to_complete_required'], 'required'],
            [['type_id', 'milestone_id', 'is_file_reqiured', 'is_affects_to_cycle_time', 'time_to_complete_required', 'order_no'], 'integer'],
            [['milestone_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoMilestones::className(), 'targetAttribute' => ['milestone_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoTypes::className(), 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Тип проекта',
            'milestone_id' => 'Этап проекта',
            'is_file_reqiured' => 'Требуется ли предоставление минимум одного файла для закрытия этапа',
            'is_affects_to_cycle_time' => 'Влияет ли на расчет общей продолжительности для завершения проекта',
            'time_to_complete_required' => 'Время для завершения этапа в днях',
            'order_no' => '№ п/п',
            // вычисляемые поля
            'typeName' => 'Тип проекта',
            'milestoneName' => 'Этап проекта',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(EcoTypes::className(), ['id' => 'type_id']);
    }

    /**
     * Возвращает наименование типа проектов по экологии.
     * @return string
     */
    public function getTypeName()
    {
        return !empty($this->type) ? $this->type->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMilestone()
    {
        return $this->hasOne(EcoMilestones::className(), ['id' => 'milestone_id']);
    }

    /**
     * Возвращает наименование этапа проектов.
     * @return string
     */
    public function getMilestoneName()
    {
        return !empty($this->milestone) ? $this->milestone->name : '';
    }
}
