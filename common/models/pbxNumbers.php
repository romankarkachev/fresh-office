<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "numbers".
 *
 * @property integer $id
 * @property integer $abc
 * @property integer $of
 * @property integer $to
 * @property integer $capacity
 * @property string $operator
 * @property string $region
 *
 * @property Cdr[] $cdrs
 */
class pbxNumbers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'numbers';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_asterisk');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['abc', 'of', 'to', 'capacity'], 'integer'],
            [['operator', 'region'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'abc' => 'Abc',
            'of' => 'Of',
            'to' => 'To',
            'capacity' => 'Capacity',
            'operator' => 'Operator',
            'region' => 'Region',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCdrs()
    {
        return $this->hasMany(Cdr::className(), ['number_id' => 'id']);
    }
}
