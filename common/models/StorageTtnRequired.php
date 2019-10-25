<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "storage_ttn_required".
 *
 * @property int $id
 * @property int $type Тип сущности (1 - контрагент, 2 - ответственный, 3 - проект)
 * @property int $entity_id Идентификатор сущности
 */
class StorageTtnRequired extends \yii\db\ActiveRecord
{
    /**
     * Значения для поля "Тип"
     */
    const TYPE_КОНТРАГЕНТ = 1;
    const TYPE_ОТВЕТСТВЕННЫЙ = 2;
    const TYPE_ПРОЕКТ = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'storage_ttn_required';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'entity_id'], 'integer'],
            [['entity_id'], 'required'],
            [['type'], 'unique', 'targetAttribute' => ['type', 'entity_id'], 'comboNotUnique' => 'Сущность уже была добавлена ранее.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Тип сущности', // 1 - контрагент, 2 - ответственный, 3 - проект
            'entity_id' => 'Идентификатор сущности',
        ];
    }

    /**
     * Возвращает массив возможных значений для поля "Тип".
     * @return array
     */
    public static function fetchTypes()
    {
        return [
            [
                'id' => self::TYPE_КОНТРАГЕНТ,
                'name' => 'Контрагент',
            ],
            [
                'id' => self::TYPE_ОТВЕТСТВЕННЫЙ,
                'name' => 'Ответственный',
            ],
            [
                'id' => self::TYPE_ПРОЕКТ,
                'name' => 'Проект',
            ],
        ];
    }

    /**
     * Делает выборку значений для поля "Тип сущности" и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfTypesForSelect2()
    {
        return ArrayHelper::map(self::fetchTypes(), 'id', 'name');
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
}
