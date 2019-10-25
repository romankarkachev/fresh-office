<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "eco_mc_tp".
 *
 * @property int $id
 * @property int $mc_id Договор сопровождения
 * @property int $report_id Отчет
 * @property string $date_deadline Крайний срок сдачи
 * @property string $date_fact Фактический срок сдачи
 *
 * @property string $reportName
 *
 * @property EcoReportsKinds $report
 * @property EcoMc $mc
 */
class EcoMcTp extends \yii\db\ActiveRecord
{
    /**
     * Идентификаторы элементов страницы
     */
    const DOM_IDS = [
        // блок с регламентированными отчетами
        'BLOCK_ID' => 'block-reports',
        // блоки со значениями для нового отчета договора (всегда со счетчиком)
        'ROW_ID' => 'report-row',
        // значок загрузки, появляющийся при добавлении нового отчета
        'PRELOADER' => 'reports-preloader',
        // кнопка "Добавить"
        'ADD_BUTTON' => 'btnNewReport',
        // кнопки удаления отчета при создании нового договора
        'DELETE_BUTTON' => 'btnDeleteNewReport',
        // форма для интерактивного добавления отчета
        'PJAX_FORM_ID' => 'frmNewReport',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eco_mc_tp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['report_id', 'date_deadline'], 'required'], // поле mc_id сделано здесь необязательным, но в базе оно не может быть NULL
            [['mc_id', 'report_id'], 'integer'],
            [['date_deadline', 'date_fact'], 'safe'],
            [['report_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoReportsKinds::className(), 'targetAttribute' => ['report_id' => 'id']],
            [['mc_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoMc::className(), 'targetAttribute' => ['mc_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mc_id' => 'Договор сопровождения',
            'report_id' => 'Отчет',
            'date_deadline' => 'Крайний срок сдачи',
            'date_fact' => 'Фактический срок сдачи',
            // вычисляемые поля
            'reportName' => 'Отчет',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMc()
    {
        return $this->hasOne(EcoMc::class, ['id' => 'mc_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReport()
    {
        return $this->hasOne(EcoReportsKinds::class, ['id' => 'report_id']);
    }

    /**
     * Возвращает наименование регламентированного отчета.
     * @return string
     */
    public function getReportName()
    {
        return !empty($this->report) ? $this->report->name : '';
    }
}
