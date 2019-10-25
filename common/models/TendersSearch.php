<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Tenders;

/**
 * TendersSearch represents the model behind the search form of `common\models\Tenders`.
 */
class TendersSearch extends Tenders
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'tk_id', 'state_id', 'org_id', 'fo_ca_id', 'tp_id', 'manager_id', 'responsible_id', 'placed_at', 'date_stop', 'date_sumup', 'date_auction', 'ta_id', 'is_notary_required', 'is_contract_edit', 'deferral', 'is_contract_approved', 'lr_id'], 'integer'],
            [['law_no', 'oos_number', 'revision', 'title', 'fo_ca_name', 'conditions', 'date_complete', 'comment', 'we'], 'safe'],
            [['amount_start', 'amount_offer', 'amount_fo', 'amount_fc'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @param $route string маршрут для сортировки и постраничного перехода
     * @return ActiveDataProvider
     */
    public function search($params, $route = 'tenders')
    {
        $tableName = Tenders::tableName();
        $query = Tenders::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'tk_id',
                    'law_no',
                    'oos_number',
                    'revision',
                    'state_id',
                    'title',
                    'org_id',
                    'fo_ca_id',
                    'fo_ca_name',
                    'tp_id',
                    'we',
                    'manager_id',
                    'responsible_id',
                    'conditions',
                    'placed_at',
                    'date_complete',
                    'date_stop',
                    'date_sumup',
                    'date_auction',
                    'ta_id',
                    'is_notary_required',
                    'is_contract_edit',
                    'amount_start',
                    'amount_offer',
                    'amount_fo',
                    'amount_fc',
                    'deferral',
                    'is_contract_approved',
                    'lr_id',
                    'comment',
                    'createdByProfileName' => [
                        'asc' => ['createdByProfile.name' => SORT_ASC],
                        'desc' => ['createdByProfile.name' => SORT_DESC],
                    ],
                    'stateName' => [
                        'asc' => [TendersStates::tableName() . '.name' => SORT_ASC],
                        'desc' => [TendersStates::tableName() . '.name' => SORT_DESC],
                    ],
                    'orgName' => [
                        'asc' => [Organizations::tableName() . '.name' => SORT_ASC],
                        'desc' => [Organizations::tableName() . '.name' => SORT_DESC],
                    ],
                    'managerProfileName' => [
                        'asc' => ['managerProfile.name' => SORT_ASC],
                        'desc' => ['managerProfile.name' => SORT_DESC],
                    ],
                    'responsibleProfileName' => [
                        'asc' => ['responsibleProfile.name' => SORT_ASC],
                        'desc' => ['responsibleProfile.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['org']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // менеджеры по продажам видят только свои тендеры (только те, которые они сами создавали)
        if (Yii::$app->user->can('sales_department_manager')) {
            $query->where([
                $tableName . '.created_by' => Yii::$app->user->id,
            ]);
        }
        else {
            if (Yii::$app->user->can('tenders_manager')) {
                $query->where([
                    'or',
                    [$tableName . '.created_by' => Yii::$app->user->id],
                    $tableName . '.state_id >= ' . TendersStates::STATE_СОГЛАСОВАНА,
                ]);
            }
            elseif (Yii::$app->user->can('sales_department_head')) {
                // руководитель отдела продаж должен видеть тендеры, созданные менеджерами
                $query->joinWith(['createdByRole']);
                $query->andWhere([
                    'item_name' => 'sales_department_manager',
                ]);
            }
            else {
                $query->andFilterWhere([
                    'created_by' => $this->created_by,
                ]);
            }
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'tk_id' => $this->tk_id,
            'state_id' => $this->state_id,
            'org_id' => $this->org_id,
            'fo_ca_id' => $this->fo_ca_id,
            'tp_id' => $this->tp_id,
            'manager_id' => $this->manager_id,
            'responsible_id' => $this->responsible_id,
            'placed_at' => $this->placed_at,
            'date_complete' => $this->date_complete,
            'date_stop' => $this->date_stop,
            'date_sumup' => $this->date_sumup,
            'date_auction' => $this->date_auction,
            'ta_id' => $this->ta_id,
            'is_notary_required' => $this->is_notary_required,
            'is_contract_edit' => $this->is_contract_edit,
            'amount_start' => $this->amount_start,
            'amount_offer' => $this->amount_offer,
            'amount_fo' => $this->amount_fo,
            'amount_fc' => $this->amount_fc,
            'deferral' => $this->deferral,
            'is_contract_approved' => $this->is_contract_approved,
            'lr_id' => $this->lr_id,
        ]);

        $query->andFilterWhere(['like', 'law_no', $this->law_no])
            ->andFilterWhere(['like', 'oos_number', $this->oos_number])
            ->andFilterWhere(['like', 'revision', $this->revision])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'fo_ca_name', $this->fo_ca_name])
            ->andFilterWhere(['like', 'we', $this->we])
            ->andFilterWhere(['like', 'conditions', $this->conditions])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
