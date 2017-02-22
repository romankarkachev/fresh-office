<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_excludes".
 *
 * @property integer $id
 * @property string $name
 */
class ProductsExcludes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products_excludes';
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
            'name' => 'Часть наименования',
        ];
    }
}
