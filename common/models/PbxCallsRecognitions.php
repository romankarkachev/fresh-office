<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "cdr_yrr".
 *
 * @property int $id
 * @property int $created_at
 * @property int $call_id Звонок
 * @property string $rr Результат распознавания
 * @property string $ffp Полный путь к файлу
 * @property string $fn Имя файла
 *
 * @property PbxCalls $call
 */
class PbxCallsRecognitions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cdr_yrr';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_asterisk');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['call_id', 'rr'], 'required'],
            [['created_at', 'call_id'], 'integer'],
            [['rr', 'ffp', 'fn'], 'string'],
            [['call_id'], 'exist', 'skipOnError' => true, 'targetClass' => PbxCalls::className(), 'targetAttribute' => ['call_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'call_id' => 'Звонок',
            'rr' => 'Результат распознавания',
            'ffp' => 'Полный путь к файлу',
            'fn' => 'Имя файла',
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
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
                'preserveNonEmptyValues' => true,
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCall()
    {
        return $this->hasOne(PbxCalls::className(), ['id' => 'call_id']);
    }
}
