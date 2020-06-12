<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Связка платежного ордера и проекта по экологии.
 *
 * @property int $id
 * @property int $po_id Платежный ордер
 * @property int $ep_id Проект по экологии
 *
 * @property EcoProjects $ep
 * @property Po $po
 */
class PoEp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po_ep';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['po_id', 'ep_id'], 'required'],
            [['po_id', 'ep_id'], 'integer'],
            [['ep_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoProjects::class, 'targetAttribute' => ['ep_id' => 'id']],
            [['po_id'], 'exist', 'skipOnError' => true, 'targetClass' => Po::class, 'targetAttribute' => ['po_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $result = (new Po)->attributeLabels();
        unset($result['id']);

        return ArrayHelper::merge($result, [
            'id' => 'ID',
            'po_id' => 'Платежный ордер',
            'ep_id' => 'Проект по экологии',
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPo()
    {
        return $this->hasOne(Po::class, ['id' => 'po_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEi()
    {
        return $this->hasOne(PoEi::class, ['id' => 'ei_id'])->via('po');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEp()
    {
        return $this->hasOne(EcoProjects::class, ['id' => 'ep_id']);
    }
}
