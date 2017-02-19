<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "handling_kinds".
 *
 * @property integer $id
 * @property string $name
 * @property integer $is_active
 */
class HandlingKinds extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'handling_kinds';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['is_active'], 'integer'],
            [['name'], 'string', 'max' => 150],
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
            'is_active' => '0 - отключен, 1 - активен',
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getDocuments()->count() > 0) return true;

        return false;
    }
}
