<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tfk_fields".
 *
 * @property int $id
 * @property int $kind_id Форма
 * @property string $alias Псевдоним
 * @property string $name Наименование
 * @property string $description Описание
 * @property string $widget Виджет, применяемый для ввода значения в это поле
 *
 * @property string $kindName
 * @property string $widgetName
 * @property string $widgetPlaceholder
 *
 * @property TenderFormsKinds $kind
 */
class TenderFormsKindsFields extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tfk_fields';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['kind_id', 'alias', 'name'], 'required'],
            [['kind_id'], 'integer'],
            [['alias', 'name', 'description'], 'string', 'max' => 255],
            [['widget'], 'string', 'max' => 40],
            [['kind_id', 'alias'], 'unique', 'targetAttribute' => ['kind_id', 'alias'], 'message' => 'Такое поле уже включено в форму.'],
            [['kind_id'], 'exist', 'skipOnError' => true, 'targetClass' => TenderFormsKinds::class, 'targetAttribute' => ['kind_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'kind_id' => 'Форма',
            'alias' => 'Псевдоним',
            'name' => 'Наименование',
            'description' => 'Описание',
            'widget' => 'Виджет, применяемый для ввода значения в это поле',
            // вычисляемые поля
            'kindName' => 'Форма',
        ];
    }

    /**
     * Возвращает допустимые виджеты.
     * @return array
     */
    public static function fetchWidgets()
    {
        return [
            [
                'id' => 'string',
                'name' => 'Текст в одну строку',
                'placeholder' => 'Введите строку',
            ],
            [
                'id' => 'text',
                'name' => 'Многострочный текст',
                'placeholder' => 'Введите текст',
            ],
            [
                'id' => 'date',
                'name' => 'Дата',
                'placeholder' => '- выберите дату -',
            ],
            [
                'id' => 'phone',
                'name' => 'Номер телефона',
                'placeholder' => 'Введите номер телефона',
            ],
        ];
    }

    /**
     * Делает выборку типов виджетов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfWidgetsForSelect2()
    {
        return ArrayHelper::map(self::fetchWidgets(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKind()
    {
        return $this->hasOne(TenderFormsKinds::class, ['id' => 'kind_id']);
    }

    /**
     * Возвращает наименование формы.
     * @return string
     */
    public function getKindName()
    {
        return $this->kind ? $this->kind->name : '';
    }

    /**
     * Возвращает наименование виджета.
     * @return string
     */
    public function getWidgetName()
    {
        $sourceTable = self::fetchWidgets();
        $key = array_search($this->widget, array_column($sourceTable, 'id'));
        if (false !== $key) {
            return $sourceTable[$key]['name'];
        }
        else {
            return '';
        }
    }

    /**
     * Возвращает placeholder виджета.
     * @return string
     */
    public function getWidgetPlaceholder()
    {
        $sourceTable = self::fetchWidgets();
        $key = array_search($this->widget, array_column($sourceTable, 'id'));
        if (false !== $key) {
            return $sourceTable[$key]['placeholder'];
        }
        else {
            return '';
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVk()
    {
        return $this->hasOne(TenderFormsVarietiesKinds::class, ['kind_id' => 'kind_id']);
    }
}
