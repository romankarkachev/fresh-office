<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "eco_reports_kinds".
 *
 * @property int $id
 * @property string $name Наименование
 * @property string $gov_agency Наименование органа, принимающего отчет
 * @property int $periodicity Периодичность подачи отчета
 * @property int $sort Номер по порядку
 *
 * @property string $periodicityName
 *
 * @property EcoMcTp[] $ecoMcTps
 */
class EcoReportsKinds extends \yii\db\ActiveRecord
{
    /**
     * Возможные значения для поля "Периодичность"
     */
    const PERIODICITY_МЕСЯЦ = 1;
    const PERIODICITY_КВАРТАЛ = 2;
    const PERIODICITY_ПОЛУГОДИЕ = 3;
    const PERIODICITY_ГОД = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eco_reports_kinds';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['periodicity', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['gov_agency'], 'string', 'max' => 255],
            [['name', 'gov_agency', 'periodicity'], 'trim'],
            [['name', 'gov_agency', 'periodicity'], 'default', 'value' => null],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'gov_agency' => 'Наименование органа, принимающего отчет',
            'periodicity' => 'Периодичность подачи отчета',
            'sort' => 'Номер по порядку',
            // вычисляемые поля
            'periodicityName' => 'Периодичность',
        ];
    }

    /**
     * Возвращает набор возможных значений для поля "Периодичность".
     * @return array
     */
    public static function fetchPeriodicities()
    {
        return [
            [
                'id' => self::PERIODICITY_МЕСЯЦ,
                'name' => 'Месяц',
            ],
            [
                'id' => self::PERIODICITY_КВАРТАЛ,
                'name' => 'Квартал',
            ],
            [
                'id' => self::PERIODICITY_ПОЛУГОДИЕ,
                'name' => 'Полугодие',
            ],
            [
                'id' => self::PERIODICITY_ГОД,
                'name' => 'Год',
            ],
        ];
    }

    /**
     * Делает выборку периодичностей сдачи отчетов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfPeriodicitiesForSelect2()
    {
        return ArrayHelper::map(self::fetchPeriodicities() , 'id', 'name');
    }

    /**
     * Делает выборку разновидностей регламентированных отчетов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getEcoMcTps()->count() > 0) return true;

        return false;
    }

    /**
     * Возвращает наименование периодичности подачи отчета.
     * @return string
     */
    public function getPeriodicityName()
    {
        if (empty($this->periodicity)) {
            return '<не определена>';
        }

        $sourceTable = self::fetchPeriodicities();
        $key = array_search($this->periodicity, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoMcTps()
    {
        return $this->hasMany(EcoMcTp::class, ['report_id' => 'id']);
    }
}
