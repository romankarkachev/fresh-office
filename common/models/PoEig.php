<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "po_eig".
 *
 * @property int $id
 * @property string $name Наименование
 *
 * @property PoEi[] $poEis
 */
class PoEig extends \yii\db\ActiveRecord
{
    const ГРУППА_ТРАНСПОРТ = 1;
    const ГРУППА_ЗАРПЛАТА = 2;
    const ГРУППА_ЭКОЛОГИЯ = 10;
    const ГРУППА_НАЛОГИ = 11;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po_eig';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 150],
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
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getPoEis()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку групп статей расходов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoEis()
    {
        return $this->hasMany(PoEi::class, ['group_id' => 'id']);
    }
}
