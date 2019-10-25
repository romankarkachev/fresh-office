<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * @property integer $org_id
 * @property integer $cp_id
 *
 * @property Organizations $organisation
 * @property CorrespondencePackages $cp
 */
class PrintEnvelopeForm extends Model
{
    /**
     * @var integer идентификатор организации, от имени которой отправляется письмо
     */
    public $org_id;

    /**
     * @var integer идентификатор пакета корреспонденции
     */
    public $cp_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org_id', 'cp_id'], 'required'],
            [['org_id', 'cp_id'], 'integer'],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::className(), 'targetAttribute' => ['org_id' => 'id']],
            [['cp_id'], 'exist', 'skipOnError' => true, 'targetClass' => CorrespondencePackages::className(), 'targetAttribute' => ['cp_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'org_id' => 'Организация',
            'cp_id' => 'Пакет корреспонденции',
        ];
    }

    /**
     * @return Organizations
     */
    public function getOrganisation()
    {
        return Organizations::findOne(['id' => $this->org_id]);
    }

    /**
     * @return CorrespondencePackages
     */
    public function getCp()
    {
        return CorrespondencePackages::findOne(['id' => $this->cp_id]);
    }
}
