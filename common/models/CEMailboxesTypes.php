<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ce_mailboxes_types".
 *
 * @property integer $id
 * @property string $name
 *
 * @property CEMailboxes[] $mailboxes
 */
class CEMailboxesTypes extends \yii\db\ActiveRecord
{
    const TYPE_СТОРОННИЙ = 1;
    const TYPE_КОРПОРАТИВНЫЙ = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ce_mailboxes_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
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
     * Делает выборку типов почтовых ящиков и возвращает в виде массива.
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
        return $this->hasMany(CEMailboxes::className(), ['type_id' => 'id']);
    }
}
