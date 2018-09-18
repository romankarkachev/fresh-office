<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ce_mailboxes_categories".
 *
 * @property integer $id
 * @property string $name
 *
 * @property CeMailboxes[] $mailboxes
 */
class CEMailboxesCategories extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ce_mailboxes_categories';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
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
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getMailboxes()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку категорий почтовых ящиков и возвращает в виде массива.
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
    public function getMailboxes()
    {
        return $this->hasMany(CEMailboxes::className(), ['category_id' => 'id']);
    }
}
