<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "documents_types".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Edf[] $edfs
 */
class DocumentsTypes extends \yii\db\ActiveRecord
{
    const TYPE_ДОГОВОР = 1;
    const TYPE_ДОП_СОГЛАШЕНИЕ = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'documents_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
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
     * Делает выборку типов документов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdfs()
    {
        return $this->hasMany(Edf::className(), ['type_id' => 'id']);
    }
}
