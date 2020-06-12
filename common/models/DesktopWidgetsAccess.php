<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "desktop_widgets_access".
 *
 * @property int $id
 * @property int $widget_id Виджет
 * @property int $type Тип (1 - роль, 2 - пользователь)
 * @property string $entity_id Идентификатор сущности
 *
 * @property string $widgetName
 * @property string $typeName
 * @property string $entityName
 *
 * @property DesktopWidgets $widget
 */
class DesktopWidgetsAccess extends \yii\db\ActiveRecord
{
    /**
     * Возможные значения для поля "Тип"
     */
    const TYPE_ROLE = 1;
    const TYPE_USER = 2;

    /**
     * Идентификаторы элементов страницы
     */
    const DOM_IDS = [
        // блок с идентификатором сущности
        'BLOCK_ENTITY_ID' => 'block-entity',
        // форма для интерактивного добавления
        'PJAX_FORM_ID' => 'frmNewUsage',
        // метка поля "Тип"
        'LABEL_ID' => 'label-type',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'desktop_widgets_access';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['widget_id', 'type', 'entity_id'], 'required'],
            [['widget_id', 'type'], 'integer'],
            [['entity_id'], 'string', 'max' => 64],
            [['widget_id'], 'exist', 'skipOnError' => true, 'targetClass' => DesktopWidgets::class, 'targetAttribute' => ['widget_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'widget_id' => 'Виджет',
            'type' => 'Тип', // 1 - роль, 2 - пользователь
            'entity_id' => 'Идентификатор сущности',
            'typeName' => 'Тип',
            'entityName' => 'Наименование',
        ];
    }

    /**
     * Возвращает массив с возможными значениями поля "Тип".
     * @return array
     */
    public static function fetchTypes()
    {
        return [
            [
                'id' => self::TYPE_ROLE,
                'name' => 'Роль',
            ],
            [
                'id' => self::TYPE_USER,
                'name' => 'Пользователь',
            ],
        ];
    }

    /**
     * Делает выборку значений для поля "Тип" и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfTypesForSelect2()
    {
        return ArrayHelper::map(self::fetchTypes(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWidget()
    {
        return $this->hasOne(DesktopWidgets::class, ['id' => 'widget_id']);
    }

    /**
     * Возвращает наименование виджета.
     * @return string
     */
    public function getWidgetName()
    {
        return !empty($this->widget) ? $this->widget->name : '';
    }

    /**
     * Возвращает наименование типа сущности.
     * @return string
     */
    public function getTypeName()
    {
        $sourceTable = self::fetchTypes();
        $key = array_search($this->type, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }

    /**
     * Возвращает наименование сущности.
     * @return string
     */
    public function getEntityName()
    {
        switch ($this->type) {
            case DesktopWidgetsAccess::TYPE_ROLE:
                $model = AuthItem::findOne($this->entity_id);
                return $model->description;
                break;
            case DesktopWidgetsAccess::TYPE_USER:
                $model = User::findOne($this->entity_id);
                return $model->profile->name;
                break;
        }
    }
}
