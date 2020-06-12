<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tenders_loss_reasons".
 *
 * @property int $id
 * @property string $name Наименование
 *
 * @property TendersLr[] $tendersLrs
 */
class TendersLossReasons extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_loss_reasons';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 30],
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
     * Делает выборку возможных причин проигрышей в конкурсах и возвращает в виде массива.
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
        if ($this->getTendersLrs()->count() > 0) return true;

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTendersLrs()
    {
        return $this->hasMany(TendersLr::class, ['lr_id' => 'id']);
    }
}
