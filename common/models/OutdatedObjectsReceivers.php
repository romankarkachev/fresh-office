<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "outdated_objects_receivers".
 *
 * @property int $id
 * @property int $section Раздел учета (1 - проекты по экологии, 2 - договоры по экологии, 3 - запросы на транспорт, 4 - пакеты корреспонденции)
 * @property int $time Время в минутах (сколько статус не меняется уже)
 * @property string $receiver E-mail
 *
 * @property string $sectionName
 */
class OutdatedObjectsReceivers extends \yii\db\ActiveRecord
{
    /**
     * Возможные значения для поля "Раздел учета"
     */
    const SECTION_ЭКО_ПРОЕКТЫ = 1;
    const SECTION_ЭКО_ДОГОВОРЫ = 2;
    const SECTION_ЗАПРОСЫ_ТРАНСПОРТА = 3;
    const SECTION_ПАКЕТЫ = 4;

    /**
     * @var integer единица измерения периодичности
     */
    public $periodicity;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outdated_objects_receivers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['section', 'time', 'periodicity'], 'integer'],
            [['receiver'], 'string', 'max' => 255],
            ['receiver', 'email'],
            [['section'], 'unique', 'targetAttribute' => ['section', 'receiver'], 'message' => 'На указанный E-mail уже отправляются уведомления о просроченных объектах по этому разделу учета.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'section' => 'Раздел учета',
            'time' => 'Время',
            'receiver' => 'E-mail',
            'periodicity' => 'Ед. изм.',
            'sectionName' => 'Раздел учета',
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
                'id' => self::SECTION_ЭКО_ПРОЕКТЫ,
                'name' => 'Проекты по экологии',
            ],
            [
                'id' => self::SECTION_ЭКО_ДОГОВОРЫ,
                'name' => 'Договоры по экологии',
            ],
            [
                'id' => self::SECTION_ЗАПРОСЫ_ТРАНСПОРТА,
                'name' => 'Запросы транспорта',
                'time_limit' => 259200,
            ],
            [
                'id' => self::SECTION_ПАКЕТЫ,
                'name' => 'Пакеты корреспонденции',
                'time_limit' => 604800,
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
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!empty($this->time)) {
                switch ($this->periodicity) {
                    case NotifReceiversStatesNotChangedByTime::PERIOD_MINUTE:
                        $this->time *= 60;
                        break;
                    case NotifReceiversStatesNotChangedByTime::PERIOD_HOUR:
                        $this->time *= 3600;
                        break;
                    case NotifReceiversStatesNotChangedByTime::PERIOD_DAY:
                        $this->time *= 3600 * 24;
                        break;
                }
            }

            return true;
        }
        return false;
    }

    /**
     * Возвращает наименование раздела учета.
     * @return string
     */
    public function getSectionName()
    {
        if (null === $this->section) {
            return '<не определен>';
        }

        $sourceTable = self::fetchSections();
        $key = array_search($this->section, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }
}
