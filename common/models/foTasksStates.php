<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "PRIZNAK_CONTACT".
 *
 * @property int $ID_PRIZNAK_CONTACT
 * @property string $DISCRIPTION_PRIZNAK_CONTACT
 * @property resource $IMAGE_PRIZNAK_CONTACT
 * @property int $COLOR_SCHLURER
 * @property string $URL_IMG
 * @property int $IS_FINAL
 *
 * @property LISTCONTACTCOMPANY[] $lISTCONTACTCOMPANies
 */
class foTasksStates extends \yii\db\ActiveRecord
{
    const STATE_ЗАПЛАНИРОВАНА = 1;
    const STATE_ВЫПОЛНЕНА = 2;
    const STATE_В_ПРОЦЕССЕ = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'PRIZNAK_CONTACT';
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
            [['DISCRIPTION_PRIZNAK_CONTACT'], 'required'],
            [['DISCRIPTION_PRIZNAK_CONTACT', 'IMAGE_PRIZNAK_CONTACT', 'URL_IMG'], 'string'],
            [['COLOR_SCHLURER', 'IS_FINAL'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID_PRIZNAK_CONTACT' => 'Id  Priznak  Contact',
            'DISCRIPTION_PRIZNAK_CONTACT' => 'Discription  Priznak  Contact',
            'IMAGE_PRIZNAK_CONTACT' => 'Image  Priznak  Contact',
            'COLOR_SCHLURER' => 'Color  Schlurer',
            'URL_IMG' => 'Url  Img',
            'IS_FINAL' => 'Is  Final',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTCONTACTCOMPANies()
    {
        return $this->hasMany(LISTCONTACTCOMPANY::className(), ['ID_PRIZNAK_CONTACT' => 'ID_PRIZNAK_CONTACT']);
    }
}
