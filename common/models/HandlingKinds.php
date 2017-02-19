<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "handling_kinds".
 *
 * @property integer $id
 * @property string $name
 * @property integer $is_active
 *
 * @property DocumentsHk[] $documentsHks
 */
class HandlingKinds extends \yii\db\ActiveRecord
{
    /**
     * Сбор.
     */
    const HK_GATHERING = 1;

    /**
     * Транспортирование.
     */
    const HK_TRANSPORTATION = 2;

    /**
     * Обработка.
     */
    const HK_PROCESSING = 3;

    /**
     * Утилизация.
     */
    const HK_UTILIZATION = 4;

    /**
     * Обезвреживание.
     */
    const HK_NEUTRALIZATION = 5;

    /**
     * Размещение.
     */
    const HK_PLACEMENT = 6;

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
        if ($this->getDocumentsHks()->count() > 0) return true;

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentsHks()
    {
        return $this->hasMany(DocumentsHk::className(), ['hk_id' => 'id']);
    }
}
