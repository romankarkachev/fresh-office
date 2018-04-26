<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "projects_ratings".
 *
 * @property integer $id
 * @property integer $rated_at
 * @property integer $rated_by
 * @property integer $ca_id
 * @property integer $project_id
 * @property string $rate
 * @property string $comment
 *
 * @property User $ratedBy
 */
class ProjectsRatings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'projects_ratings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'rate'], 'required'],
            [['rated_at', 'rated_by', 'ca_id', 'project_id'], 'integer'],
            [['rate'], 'number'],
            [['comment'], 'string'],
            [['rated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['rated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rated_at' => 'Дата и время оценки',
            'rated_by' => 'Кто оценил',
            'ca_id' => 'Контрагент',
            'project_id' => 'Проект',
            'rate' => 'Оценка',
            'comment' => 'Замечания для не самой наивысшей оценки',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['rated_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['rated_by'],
                ],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'rated_by']);
    }
}
