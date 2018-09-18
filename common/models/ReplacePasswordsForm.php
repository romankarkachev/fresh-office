<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use dektrium\user\helpers\Password;

/**
 * @property string $conditionRoleId условие для отбора по роли
 * @property bool $conditionOnlyFO условие для отбора только по связанным с Fresh Office пользователям
 * @property integer $passwordLength количество символов в новом пароле
 */
class ReplacePasswordsForm extends Model
{
    public $conditionRoleId;

    public $conditionOnlyFO;

    public $passwordLength;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['passwordLength'], 'required'],
            [['conditionRoleId'], 'safe'],
            [['passwordLength', 'conditionOnlyFO'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'conditionRoleId' => 'Роль',
            'conditionOnlyFO' => 'Только Fresh Office',
            'passwordLength' => 'К-во символов',
        ];
    }

    /**
     * Выполняет отбор пользователей по условиям.
     * @param array $params
     * @return ArrayDataProvider
     */
    public function search($params)
    {
        $query = User::find();
        $query->select([
            'id' => 'user.id',
            'fo_id' => 'profile.fo_id',
            'username',
            'profileName' => 'profile.name',
            'roleName' => 'auth_item.description',
        ]);
        $query->leftJoin('profile', 'profile.user_id = user.id');
        $query->leftJoin('auth_assignment', 'auth_assignment.user_id = user.id');
        $query->leftJoin('auth_item', 'auth_item.name = auth_assignment.item_name');
        $query->orderBy('profile.name');

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return new ArrayDataProvider();
        }

        // длина пароля - обязательный реквизит
        if (empty($this->passwordLength)) {
            // если она не указана, то задаем ее тут
            $this->passwordLength = 6;
        }

        // условие отбора только по тем пользователям, которые имеют сопоставление с Fresh Office
        if (!empty($this->conditionOnlyFO)) {
            $query->andWhere(['is not', 'fo_id', null]);
        }

        $query->andFilterWhere(['like', 'auth_item.name', $this->conditionRoleId]);

        // выполним запрос
        $data = $query->asArray()->all();

        // придумаем новые пароли всем пользователям в выборке
        foreach ($data as $key => $user) {
            $data[$key]['newPassword'] = Password::generate(intval($this->passwordLength));
        }

        return new ArrayDataProvider([
            'modelClass' => 'common\models\ReplaceUsersPasswords',
            'allModels' => $data,
            'key' => 'id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => false,
        ]);
    }

    /**
     * Выполняет обновление значений поля "Пароль" пользователей, отобранных по условиям.
     */
    public function executeReplacing()
    {

    }
}
