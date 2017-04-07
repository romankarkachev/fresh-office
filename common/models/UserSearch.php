<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use dektrium\user\models\UserSearch as BaseUserSearch;

/**
 * UserSearch represents the model behind the search form about User.
 */
class UserSearch extends BaseUserSearch
{
    /**
     * Реквизит отбора (поиск по всем полям).
     * @var string
     */
    public $searchEntire;

    public function rules()
    {
        $result = parent::rules();

        $result[] = [['searchEntire'], 'safe'];

        return $result;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['searchEntire'] = 'Значение для поиска по всем полям';

        return $labels;
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = $this->finder->getUserQuery();
        $query->select('*, `user`.`id` AS `id`, (
            SELECT `name` FROM `profile`
            WHERE `profile`.`user_id` = `user`.`id`
        ) AS `profileName`, (
            SELECT `description` FROM `auth_item`
            INNER JOIN `auth_assignment` `aa` ON `aa`.`item_name` = `auth_item`.`name`
            WHERE `aa`.`user_id` = `user`.`id`
        ) AS `roleName`');
        $query->leftJoin('profile', 'profile.user_id = user.id');
        $query->leftJoin('auth_assignment', 'auth_assignment.user_id = user.id');
        $query->leftJoin('auth_item', 'auth_item.name = auth_assignment.item_name');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'username',
                'email',
                'profileName',
                'roleName',
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if ($this->created_at !== null) {
            $date = strtotime($this->created_at);
            $query->andFilterWhere(['between', 'created_at', $date, $date + 3600 * 24]);
        }

        $query->orFilterWhere(['like', 'auth_item.description', $this->searchEntire])
            ->orFilterWhere(['like', 'profile.name', $this->searchEntire])
            ->orFilterWhere(['like', 'user.username', $this->searchEntire])
            ->orFilterWhere(['like', 'user.email', $this->searchEntire]);

        return $dataProvider;
    }
}