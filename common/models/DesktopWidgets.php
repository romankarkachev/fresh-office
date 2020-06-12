<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "desktop_widgets".
 *
 * @property int $id
 * @property string $name Наименование
 * @property string $description Описание
 * @property string $alias Псевдоним
 *
 * @property DesktopWidgetsAccess[] $desktopWidgetsAccesses
 */
class DesktopWidgets extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'desktop_widgets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'alias'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['alias'], 'string', 'max' => 64],
            [['alias'], 'unique'],
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
            'alias' => 'Псевдоним',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDesktopWidgetsAccesses()
    {
        return $this->hasMany(DesktopWidgetsAccess::class, ['widget_id' => 'id']);
    }

    /**
     * @return string
     */
    public function renderWidget()
    {
        return $this->render;
    }
}
