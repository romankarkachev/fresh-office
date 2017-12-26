<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * @property integer $src_pt_id
 * @property integer $dest_pt_id
 */
class PackingTypeReplaceForm extends Model
{
    /**
     * @var integer искомый вид упаковки
     */
    public $src_pt_id;

    /**
     * @var integer вид упаковки, на который нужно заменить
     */
    public $dest_pt_id;

    /**
     * @var string при желании можно переименовать исходный вид упаковки
     */
    public $new_src_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['src_pt_id', 'dest_pt_id'], 'required'],
            [['new_src_name'], 'safe'],
            [['new_src_name'], 'trim'],
            [['src_pt_id', 'dest_pt_id'], 'integer'],
            [['src_pt_id'], 'exist', 'skipOnError' => true, 'targetClass' => PackingTypes::className(), 'targetAttribute' => ['src_pt_id' => 'id']],
            [['dest_pt_id'], 'exist', 'skipOnError' => true, 'targetClass' => PackingTypes::className(), 'targetAttribute' => ['dest_pt_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'src_pt_id' => 'Искомый вид упаковки',
            'dest_pt_id' => 'Новый вид упаковки',
            'new_src_name' => 'Новое наименование',
        ];
    }
}
