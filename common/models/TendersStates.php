<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tenders_states".
 *
 * @property int $id
 * @property string $name Наименование
 *
 * @property Tenders[] $tenders
 */
class TendersStates extends \yii\db\ActiveRecord
{
    /**
     * Допустимые значения
     */
    const STATE_ЧЕРНОВИК = 1;
    const STATE_СОГЛАСОВАНИЕ_РОП = 2;
    const STATE_СОГЛАСОВАНИЕ_РУКОВОДСТВОМ = 3;
    const STATE_СОГЛАСОВАНА = 4;
    const STATE_ОТКАЗ = 5;
    const STATE_В_РАБОТЕ = 6;
    const STATE_ЗАЯВКА_ПОДАНА = 7;
    const STATE_ДОЗАПРОС = 8;
    const STATE_ПОБЕДА = 9;
    const STATE_ПРОИГРЫШ = 10;
    const STATE_БЕЗ_РЕЗУЛЬТАТОВ = 11;
    const STATE_НЕ_ДОПУЩЕНЫ = 12;
    const STATE_ПАС = 13;
    const STATE_ОТМЕНЕН_ЗАКАЗЧИКОМ = 14;
    const STATE_ОПОЗДАНИЕ = 15;

    /**
     * Набор финальных статусов
     */
    const НАБОР_ФИНАЛЬНЫЕ_СТАТУСЫ = [
        self::STATE_ПОБЕДА,
        self::STATE_ПРОИГРЫШ,
        self::STATE_БЕЗ_РЕЗУЛЬТАТОВ,
        self::STATE_ОТМЕНЕН_ЗАКАЗЧИКОМ,
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_states';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
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
        ];
    }

    /**
     * Делает выборку статусов тендеров и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param bool $includeDefault добавлять ли пункт "По умолчанию"
     * @return array
     */
    public static function arrayMapForSelect2($includeDefault = null)
    {
        $result = ArrayHelper::map(self::find()->all(), 'id', 'name');
        if (!empty($includeDefault)) {
            $result = ArrayHelper::merge([
                TendersSearch::FIELD_SEARCH_STATE_DEFAULT => 'По умолчанию',
                TendersSearch::FIELD_SEARCH_STATE_IGNORE => 'Любой',
            ], $result);
        }

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTenders()
    {
        return $this->hasMany(Tenders::class, ['state_id' => 'id']);
    }
}
