<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "eco_projects_milestones".
 *
 * @property integer $id
 * @property integer $project_id
 * @property integer $milestone_id
 * @property integer $is_file_reqiured
 * @property integer $is_affects_to_cycle_time
 * @property integer $time_to_complete_required
 * @property integer $order_no
 * @property string $date_close_plan
 * @property integer $closed_at
 *
 * @property string $milestoneName
 * @property integer $filesCount количество приаттаченных к этапу файлов
 *
 * @property EcoMilestones $milestone
 * @property EcoProjects $project
 * @property EcoProjectsMilestonesFiles[] $ecoProjectsMilestonesFiles
 */
class EcoProjectsMilestones extends \yii\db\ActiveRecord
{
    /**
     * @var integer количество файлов, присоединенных к этапу (вируальное вычисляемое поле)
     */
    public $filesCount;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eco_projects_milestones';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'milestone_id'], 'required'],
            [['project_id', 'milestone_id', 'is_file_reqiured', 'is_affects_to_cycle_time', 'time_to_complete_required', 'order_no', 'closed_at'], 'integer'],
            [['date_close_plan'], 'safe'],
            [['milestone_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoMilestones::className(), 'targetAttribute' => ['milestone_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoProjects::className(), 'targetAttribute' => ['project_id' => 'id']],
            // собственные правила валидации
            ['is_file_reqiured', 'validateFilesRequired'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Проект',
            'milestone_id' => 'Этап',
            'is_file_reqiured' => 'Требуется ли предоставление минимум одного файла для закрытия этапа',
            'is_affects_to_cycle_time' => 'Влияет ли на расчет общей продолжительности для завершения проекта',
            'time_to_complete_required' => 'Время для завершения этапа в днях',
            'order_no' => 'Номер по порядку',
            'date_close_plan' => 'Планируемая дата завершения проекта',
            'closed_at' => 'Фактическая дата завершения проекта',
            // вычисляемые поля
            'projectName' => 'Тип проекта',
            'milestoneName' => 'Этап проекта',
            'filesCount' => 'Файлов',
        ];
    }

    /**
     * Удаление связанных объектов перед удалением текущего.
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $nestedRecords = EcoProjectsMilestonesFiles::find()->where(['project_milestone_id' => $this->id])->all();
            foreach ($nestedRecords as $record) $record->delete();

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function validateFilesRequired()
    {
        if ($this->is_file_reqiured && $this->getEcoProjectsMilestonesFiles()->count() == 0) {
            $this->addError('is_file_reqiured', 'Для закрытия данного этапа необходимо загрузить в систему минимум один файл!');
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!$insert && empty($this->project->ecoProjectsMilestonesPendingCount)) {
            // не осталось незавершенных этапов, так завершим же весь проект
            $this->project->updateAttributes([
                'closed_at' => time(),
            ]);
        }

        if (isset($changedAttributes['date_close_plan'])) {
            // изменился срок планируемого завершения текущего этапа
            // так пересчитаем же другие этапы
            $dateMilestoneClose = strtotime($this->date_close_plan . ' 00:00:00');
            $dateProjectClose = $dateMilestoneClose;

            foreach (self::find()->where(['project_id' => $this->project_id])->andWhere('`order_no` > ' . $this->order_no)->all() as $milestone) {
                /* @var $milestone self */

                $calcDate = $milestone->time_to_complete_required * 24 *3600;
                if ($milestone->is_affects_to_cycle_time) {
                    $dateProjectClose = $dateProjectClose + $calcDate;
                }

                $dateMilestoneClose += $calcDate;

                $milestone->updateAttributes([
                    'date_close_plan' => Yii::$app->formatter->asDate($dateMilestoneClose, 'php:Y-m-d'),
                ]);
            }

            // дату завершения всего проекта тоже не мешало бы обновить
            $this->project->updateAttributes([
                'date_close_plan' => Yii::$app->formatter->asDate($dateProjectClose, 'php:Y-m-d'),
            ]);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(EcoProjects::className(), ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMilestone()
    {
        return $this->hasOne(EcoMilestones::className(), ['id' => 'milestone_id']);
    }

    /**
     * Возвращает наименование этапа проекта.
     * @return string
     */
    public function getMilestoneName()
    {
        return !empty($this->milestone) ? $this->milestone->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoProjectsMilestonesFiles()
    {
        return $this->hasMany(EcoProjectsMilestonesFiles::className(), ['project_milestone_id' => 'id']);
    }
}
