<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tenders_stages".
 *
 * @property int $id
 * @property string $name Наименование
 *
 * @property Tenders[] $tenders
 */
class TendersStages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_stages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 150],
            ['name', 'trim'],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTenders()
    {
        return $this->hasMany(Tenders::class, ['stage_id' => 'id']);
    }
}
