<?php

namespace common\models;

use Yii;

/**
 * Виды первичных документов.
 *
 * @property integer $id
 * @property string $name
 * @property string $name_full
 */
class PadKinds extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pad_kinds';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'name_full'], 'required'],
            [['name'], 'string', 'max' => 30],
            [['name_full'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'name_full' => 'Полное наименование',
        ];
    }
}
