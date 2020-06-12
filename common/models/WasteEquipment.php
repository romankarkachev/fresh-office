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
 * @property string $description Пр-тель, страна пр-ва, марка, модель, тех. х-ки
 * @property int $year Год выпуска
 * @property int $amort_percent % амортизации
 * @property int $ownership Принадлежность (1 - собственность, 2 - арендованный)
 *
 * @property string $ownershipName
 *
 * @property TendersWe[] $tendersWasteEquipment
 */
class WasteEquipment extends \yii\db\ActiveRecord
{
    /**
     * Возможные значения для поля "Принадлежность"
     */
    const OWNERSHIP_СОБСТВЕННОСТЬ = 1;
    const OWNERSHIP_АРЕНДА = 2;

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
            [['description'], 'string'],
            [['amort_percent', 'ownership'], 'integer'],
            ['year', 'integer', 'min' => 1900, 'max' => 2100],
            [['name'], 'string', 'max' => 100],
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
            'description' => 'Описание',
            'year' => 'Год выпуска',
            'amort_percent' => '% амортизации',
            'ownership' => 'Принадлежность', // 1 - собственность, 2 - арендованный
        ];
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
     * @return array
     */
    public static function fetchOwnerships()
    {
        return [
            [
                'id' => self::OWNERSHIP_СОБСТВЕННОСТЬ,
                'name' => 'Собственность',
            ],
            [
                'id' => self::OWNERSHIP_АРЕНДА,
                'name' => 'Арендованный',
            ],
        ];
    }

    /**
     * Делает выборку разновидностей принадлежности и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfOwnershipForSelect2()
    {
        return ArrayHelper::map(self::fetchOwnerships(), 'id', 'name');
    }

    /**
     * Возвращает принадлежность.
     * @return string
     */
    public function getOwnershipName()
    {
        $sourceTable = self::fetchOwnerships();
        $key = array_search($this->ownership, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTendersWasteEquipment()
    {
        return $this->hasMany(TendersWe::class, ['we_id' => 'id']);
    }
}
