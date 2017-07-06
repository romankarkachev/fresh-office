<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "fkko".
 *
 * @property integer $id
 * @property string $fkko_code
 * @property string $fkko_name
 */
class Fkko extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fkko';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fkko_code', 'fkko_name'], 'required'],
            [['fkko_name'], 'string'],
            [['fkko_code'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fkko_code' => 'Код по ФККО-2017',
            'fkko_name' => 'Наименование',
        ];
    }
}
