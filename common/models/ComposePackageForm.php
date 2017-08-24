<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * @property string $project_ids
 * @property array $tdPad
 * @property integer $pd_id
 * @property string $track_num
 *
 * @property PostDeliveryKinds $pd
 */
class ComposePackageForm extends Model
{
    /**
     * Идентификаторы проектов.
     * @var string
     */
    public $project_ids;

    /**
     * Табличная часть предоставленных видов документов.
     * @var array
     */
    public $tpPad;

    /**
     * Идентификатор водителя.
     * @var integer
     */
    public $pd_id;

    /**
     * Идентификатор автомобиля.
     * @var integer
     */
    public $track_num;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_ids', 'pd_id'], 'required'],
            [['pd_id'], 'integer'],
            [['project_ids', 'tpPad'], 'safe'],
            [['track_num'], 'string', 'max' => 50],
            [['pd_id'], 'exist', 'skipOnError' => true, 'targetClass' => PostDeliveryKinds::className(), 'targetAttribute' => ['pd_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'project_ids' => 'Проекты',
            'tpPad' => 'Виды документов',
            'pd_id' => 'Способ доставки',
            'track_num' => 'Трек-номер',
        ];
    }

    /**
     * @return PostDeliveryKinds
     */
    public function getPd()
    {
        return PostDeliveryKinds::findOne(['id' => 'pd_id']);
    }
}
