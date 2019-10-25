<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "projects_ratings".
 *
 * @property int $id
 * @property int $rated_at Дата и время оценки
 * @property int $rated_by Кто оценил
 * @property int $ca_id Контрагент
 * @property int $project_id Проект
 * @property string $rate Оценка
 * @property string $comment Замечания для не самой наивысшей оценки
 * @property string $token Токен для голосования неавторизованными пользователями
 * @property string $email E-mail контактного лица, которому отправляется приглашение поставить оценку
 *
 * @property string $caName
 * @property string $ratedByProfileName
 *
 * @property User $ratedBy
 * @property Profile $ratedByProfile
 */
class ProjectsRatings extends \yii\db\ActiveRecord
{
    /**
     * @var integer количество проектов, которые оценил пользователь (для некоторых отчетов)
     */
    public $ratesCount;

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
            [['project_id'], 'required'],
            [['rated_at', 'rated_by', 'ca_id', 'project_id'], 'integer'],
            [['rate'], 'number'],
            [['comment'], 'string'],
            [['token'], 'string', 'max' => 32],
            [['email'], 'string', 'max' => 255],
            [['token', 'email'], 'default', 'value' => null],
            ['email', 'email'],
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
            'token' => 'Токен для голосования неавторизованными пользователями',
            'email' => 'E-mail', // контактного лица, которому отправляется приглашение поставить оценку
            // виртуальные поля
            'ratesCount' => 'Всего оценок',
            // вычисляемые поля
            'caName' => 'Контрагент',
            'ratedByProfileName' => 'Член жюри',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRatedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'rated_by']);
    }

    /**
     * Возвращает имя пользователя, который поставил оценку.
     * @return string
     */
    public function getRatedByProfileName()
    {
        return !empty($this->ratedByProfile) ? $this->ratedByProfile->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCa()
    {
        return $this->hasOne(foCompany::className(), ['ID_COMPANY' => 'ca_id']);
    }

    /**
     * Возвращает наименование контрагента.
     * @return string
     */
    public function getCaName()
    {
        return !empty($this->ca) ? $this->ca->COMPANY_NAME : '';
    }
}
