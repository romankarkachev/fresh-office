<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Справочник получателей E-mail-уведомлений о проектах и пакетах, статус которых за текущий день длительное время не
 * изменялся.
 *
 * @property int $id
 * @property int $section Раздел учета (1 - проекты, 2 - пакеты корреспонденции)
 * @property int $state_id Статус проектов для отслеживания
 * @property int $time Время в минутах (сколько статус не меняется уже)
 * @property string $receiver E-mail
 *
 * @property string $stateNameManual
 */
class NotifReceiversStatesNotChangedByTime extends \yii\db\ActiveRecord
{
    /**
     * Возможные значения для поля "Раздел учета"
     */
    const SECTION_ПРОЕКТЫ = 1;
    const SECTION_ПАКЕТЫ = 2;

    /**
     * Возможные значения для поля "Единицы измерения периодичности"
     */
    const PERIOD_MINUTE = 1;
    const PERIOD_HOUR = 2;
    const PERIOD_DAY = 3;

    /**
     * /services/notify-about-cp-outdated-by-custom-time
     */
    const НАБОР_СТАТУСОВ_ДЛЯ_ОПОВЕЩЕНИЯ_О_ПРОСРОЧЕННЫХ_ПАКЕТАХ = [
        CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ,
        CorrespondencePackagesStates::STATE_ЧЕРНОВИК,
    ];

    /**
     * @var integer единица измерения периодичности
     */
    public $periodicity;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notif_receivers_sncbt';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['section', 'state_id', 'time'], 'required'],
            [['section', 'state_id', 'time', 'periodicity'], 'integer'],
            [['receiver'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'section' => 'Раздел учета', // 1 - проекты, 2 - пакеты корреспонденции
            'state_id' => 'Статус',
            'time' => 'Время',
            'receiver' => 'E-mail',
            'periodicity' => 'Ед. изм.',
            // вычисляемые поля
            'stateName' => 'Статус',
            'stateNameManual' => 'Статус',
        ];
    }

    /**
     * Возвращает разделы учета для данного справочника.
     * @return array
     */
    public static function fetchSections()
    {
        return [
            [
                'id' => self::SECTION_ПРОЕКТЫ,
                'name' => 'Проекты',
            ],
            [
                'id' => self::SECTION_ПАКЕТЫ,
                'name' => 'Пакеты корреспонденции',
            ],
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
     * Делает выборку разделов учета и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfSectionsForSelect2()
    {
        return ArrayHelper::map(self::fetchSections(), 'id', 'name');
    }

    /**
     * Делает выборку единиц измерения периодичности и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfPeriodicityUnitsForSelect2()
    {
        return ArrayHelper::map(self::fetchPeriodicityUnits(), 'id', 'name');
    }

    /**
     * {@inheritdoc}
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
        if ($this->section == self::SECTION_ПАКЕТЫ) {
            return $this->hasOne(CorrespondencePackagesStates::class, ['id' => 'state_id']);
        }
        else {
            return $this->hasOne(foProjectsStates::class, ['ID_PRIZNAK_PROJECT' => 'state_id']);
        }
    }

    /**
     * Возвращает наименование статуса.
     * @return string
     */
    public function getStateName()
    {
        if ($this->section == self::SECTION_ПАКЕТЫ) {
            return !empty($this->state) ? $this->state->name : '';
        }
        else {
            return !empty($this->state) ? $this->state->PRIZNAK_PROJECT : '';
        }
    }

    /**
     * Возвращает наименование статуса. Определяется по массиву, переданному в параметрах.
     * @param $sourceTable array массив-источник для идентификации статуса
     * @return string
     */
    public function getStateNameManual($sourceTable)
    {
        return ArrayHelper::getValue($sourceTable, $this->state_id);
    }
}
