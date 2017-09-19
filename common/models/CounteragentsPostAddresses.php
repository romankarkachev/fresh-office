<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "counteragents_post_addresses".
 *
 * @property integer $id
 * @property integer $counteragent_id
 * @property integer $src_id
 * @property string $src_address
 * @property string $zip_code
 * @property string $address_m
 * @property string $comment
 */
class CounteragentsPostAddresses extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'counteragents_post_addresses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counteragent_id', 'src_address'], 'required'],
            [['counteragent_id', 'src_id'], 'integer'],
            [['src_address', 'address_m', 'comment'], 'string'],
            [['zip_code'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'counteragent_id' => 'Контрагент',
            'src_id' => 'ID в источнике',
            'src_address' => 'Почтовый адрес из источника',
            'zip_code' => 'Почтовый индекс',
            'address_m' => 'Нормализованный почтовый адрес',
            'comment' => 'Примечание к адресу',
        ];
    }
}
