<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Форма для формирования товарно-транспортной накладной.
 * @property Organizations $organization
 */
class DocumentTtnGenerationForm extends Model
{
    /**
     * @var string дата акта
     */
    public $date;

    /**
     * @var integer проект, к которому генерируется ТТН
     */
    public $project_id;

    /**
     * @var string наименование контрагента
     */
    public $ca_name;

    /**
     * @var string юридический адрес контрагента (по факту место загрузки)
     */
    public $ca_address;

    /**
     * @var string представитель контрагента
     */
    public $ca_contact_person;

    /**
     * @var integer наша организация, от имени которой выписываются документы
     */
    public $org_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'project_id', 'org_id', 'ca_name', 'ca_address'], 'required'],
            [['date', 'ca_name', 'ca_address', 'ca_contact_person'], 'string'],
            [['project_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date' => 'Дата акта',
            'project_id' => 'Проект',
            'ca_name' => 'Грузоотправитель',
            'ca_address' => 'Адрес места погрузки',
            'ca_contact_person' => 'Представитель контрагента',
            'org_id' => 'Организация',
        ];
    }

    /**
     * @return Organizations
     */
    public function getOrganization()
    {
        return Organizations::findOne(['id' => $this->org_id]);
    }
}
