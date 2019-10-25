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
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTenders()
    {
        return $this->hasMany(Tenders::className(), ['state_id' => 'id']);
    }
}
