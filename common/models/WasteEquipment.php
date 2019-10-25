<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "waste_equipment".
 *
 * @property int $id
 * @property string $name Наименование
 *
 * @property TendersWe[] $tendersWasteEquipment
 */
class WasteEquipment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'waste_equipment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getTendersWasteEquipment()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку видов оборудования для утилизации и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * Рендерит необходимые кнопки для управления формой.
     * @return mixed
     */
    public function renderSubmitButtons()
    {
        $siaButtons = [
            'create' => Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']),
            'save' => Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']),
        ];

        if ($this->isNewRecord) {
            return $siaButtons['create'];
        }
        else {
            $result = $siaButtons['save'];
        }

        return $result;
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
     * @return \yii\db\ActiveQuery
     */
    public function getTendersWasteEquipment()
    {
        return $this->hasMany(TendersWe::class, ['we_id' => 'id']);
    }
}
