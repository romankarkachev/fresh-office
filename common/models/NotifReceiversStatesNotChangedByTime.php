<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Справочник получателей E-mail-уведомлений о проектах, статус которых за текущий день длительное время не изменялся.
 *
 * @property integer $id
 * @property integer $state_id
 * @property integer $time
 * @property string $receiver
 */
class NotifReceiversStatesNotChangedByTime extends \yii\db\ActiveRecord
{
    /**
     * Значения для поля "Единицы измерения периодичности"
     */
    const PERIOD_MINUTE = 1;
    const PERIOD_HOUR = 2;
    const PERIOD_DAY = 3;

    /**
     * Единица измерения периодичности
     * @var integer
     */
    public $periodicity;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notif_receivers_sncbt';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['state_id', 'time'], 'required'],
            [['state_id', 'time', 'periodicity'], 'integer'],
            [['receiver'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'state_id' => 'Статус',
            'time' => 'Время',
            'receiver' => 'E-mail',
            'periodicity' => 'Ед. изм.',
            // вычисляемые поля
            'stateName' => 'Статус',
        ];
    }

    /**
     * Возвращает единицы измерения периодичности.
     * @return array
     */
    public static function fetchPeriodicityUnits()
    {
        return [
            [
                'id' => self::PERIOD_MINUTE,
                'name' => 'Минут',
            ],
            [
                'id' => self::PERIOD_HOUR,
                'name' => 'Часов',
            ],
            [
                'id' => self::PERIOD_DAY,
                'name' => 'Дней',
            ],
        ];
    }

    /**
     * Делает выборку единиц измерения периодичности и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::fetchPeriodicityUnits(), 'id', 'name');
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            switch ($this->periodicity) {
                case self::PERIOD_MINUTE:
                    $this->time *= 60;
                    break;
                case self::PERIOD_HOUR:
                    $this->time *= 3600;
                    break;
                case self::PERIOD_DAY:
                    $this->time *= 3600 * 24;
                    break;
            }

            return true;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(foProjectsStates::className(), ['ID_PRIZNAK_PROJECT' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса.
     * @return string
     */
    public function getStateName()
    {
        return !empty($this->state) ? $this->state->PRIZNAK_PROJECT : '';
    }
}
