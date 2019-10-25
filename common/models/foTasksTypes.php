<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "VID_CONTACT".
 *
 * @property int $ID_VID_CONTACT
 * @property string $DISCRIPTION_VID_CONTCT
 * @property resource $IMAGE_VID_CONTCT
 * @property string $URL_IMG
 *
 * @property CalEntryTypes[] $calEntryTypes
 * @property MANAGERS[] $managers
 * @property LISTCONTACTCOMPANY[] $lISTCONTACTCOMPANies
 */
class foTasksTypes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'VID_CONTACT';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_mssql');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['DISCRIPTION_VID_CONTCT'], 'required'],
            [['DISCRIPTION_VID_CONTCT', 'IMAGE_VID_CONTCT', 'URL_IMG'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_VID_CONTACT' => 'Id  Vid  Contact',
            'DISCRIPTION_VID_CONTCT' => 'Discription  Vid  Contct',
            'IMAGE_VID_CONTCT' => 'Image  Vid  Contct',
            'URL_IMG' => 'Url  Img',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCalEntryTypes()
    {
        return $this->hasMany(CalEntryTypes::className(), ['vid_id' => 'ID_VID_CONTACT']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagers()
    {
        return $this->hasMany(MANAGERS::className(), ['ID_MANAGER' => 'manager_id'])->viaTable('cal_entry_types', ['vid_id' => 'ID_VID_CONTACT']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTCONTACTCOMPANies()
    {
        return $this->hasMany(LISTCONTACTCOMPANY::className(), ['ID_VID_CONTACT' => 'ID_VID_CONTACT']);
    }
}
