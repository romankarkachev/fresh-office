<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * @property integer $project_id проект
 * @property integer $ca_id контрагент
 * @property integer $cp_id контактное лицо контрагента
 * @property string $email E-mail получателя письма с оценками
 */
class CustomerRatingProposalForm extends Model
{
    public $project_id;
    public $ca_id;
    public $ca_name;
    public $cp_id;
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'ca_id', 'email'], 'required'],
            [['project_id', 'ca_id', 'cp_id'], 'integer'],
            [['ca_name', 'email'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'project_id' => 'Проект',
            'ca_id' => 'Контрагент',
            'ca_name' => 'Контрагент',
            'cp_id' => 'Контактное лицо',
            'email' => 'E-mail',
        ];
    }

    /**
     * Делает выборку контактных лиц контрагента.
     * @return array
     */
    public function arrayMapOfContactPersons()
    {
        if (!empty($this->ca_id)) {
            $contactPersons = DirectMSSQLQueries::fetchCounteragentsContactPersons($this->ca_id);
            if (count($contactPersons) > 0) return ArrayHelper::map($contactPersons, 'id', 'text');
        }

        return [];
    }

    /**
     * @return array
     */
    public function fetchCompanyEmails()
    {
        if (!empty($this->ca_id)) {
            $query = foListEmailClient::find()->select('email')->where(['ID_COMPANY' => $this->ca_id])->asArray()->column();
        }

        if (empty($query)) return ['']; else return $query;
    }
}
