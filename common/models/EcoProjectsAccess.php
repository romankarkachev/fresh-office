<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "eco_projects_access".
 *
 * @property integer $id
 * @property integer $project_id
 * @property integer $user_id
 *
 * @property string $userProfileName
 *
 * @property User $user
 * @property Profile $userProfile
 * @property EcoProjects $project
 */
class EcoProjectsAccess extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eco_projects_access';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'user_id'], 'required'],
            [['project_id', 'user_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoProjects::className(), 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Проект',
            'user_id' => 'Пользователь, имеющий доступ к этому проекту',
            // вычисляемые поля
            'userProfileName' => 'Пользователь',
        ];
    }

    /**
     * Делает выборку пользователей-экологов веб-приложения и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $project_id integer проект, необходим для исключения пользователей, которые уже имеют доступ
     * @return array
     */
    public static function arrayMapForSelect2($project_id)
    {
        $tableName = User::tableName();
        return ArrayHelper::map(User::find()->select($tableName . '.*')
            ->leftJoin('`auth_assignment`', '`auth_assignment`.`user_id`=' . $tableName . '.`id`')
            ->leftJoin('`profile`', '`profile`.`user_id` = `user`.`id`')
            ->where(['`auth_assignment`.`item_name`' => 'ecologist'])
            ->andWhere(['not in', 'id', EcoProjectsAccess::find()->select('user_id')->where(['project_id' => $project_id])])
            ->orderBy('profile.name')->all(),
        'id', 'profile.name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(EcoProjects::className(), ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'user_id']);
    }

    /**
     * Возвращает имя пользователя.
     * @return string
     */
    public function getUserProfileName()
    {
        return !empty($this->userProfile) ? $this->userProfile->name : '';
    }
}
