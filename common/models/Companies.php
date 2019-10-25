<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "companies".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property string $guid GUID
 * @property string $name Наименование
 * @property string $name_full Наименование полное
 * @property string $name_short Наименование сокращенное
 * @property string $inn ИНН
 * @property string $kpp КПП
 * @property string $ogrn ОГРН(ИП)
 * @property string $address_j Юридический адрес контрагента
 * @property string $address_f Фактический адрес контрагента
 * @property string $dir_post Должность директора контрагента (им. падеж)
 * @property string $dir_name ФИО директора контрагента полностью (им. падеж)
 * @property string $dir_name_of ФИО директора контрагента полностью (род. падеж)
 * @property string $dir_name_short ФИО директора контрагента сокрщенно (им. падеж)
 * @property string $dir_name_short_of ФИО директора контрагента сокращенно (род. падеж)
 * @property string $comment Комментарий
 *
 * @property string createdByProfileName
 *
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property CompaniesBankAccounts[] $companiesBankAccounts
 * @property Po[] $pos
 */
class Companies extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'companies';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_at', 'created_by'], 'integer'],
            [['comment'], 'string'],
            [['guid'], 'string', 'max' => 36],
            [['name', 'name_full', 'name_short', 'address_j', 'address_f', 'dir_post', 'dir_name', 'dir_name_of', 'dir_name_short', 'dir_name_short_of'], 'string', 'max' => 255],
            [['inn'], 'string', 'max' => 12],
            [['kpp'], 'string', 'max' => 9],
            [['ogrn'], 'string', 'max' => 15],
            [['guid', 'ogrn'], 'unique'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'guid' => 'GUID',
            'name' => 'Наименование',
            'name_full' => 'Наименование полное',
            'name_short' => 'Наименование сокращенное',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН(ИП)',
            'address_j' => 'Юридический адрес контрагента',
            'address_f' => 'Фактический адрес контрагента',
            'dir_post' => 'Должность директора контрагента (им. падеж)',
            'dir_name' => 'ФИО директора контрагента полностью (им. падеж)',
            'dir_name_of' => 'ФИО директора контрагента полностью (род. падеж)',
            'dir_name_short' => 'ФИО директора контрагента сокрщенно (им. падеж)',
            'dir_name_short_of' => 'ФИО директора контрагента сокращенно (род. падеж)',
            'comment' => 'Комментарий',
            // вычисляемые поля
            'createdByProfileName' => 'Автор создания',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
            ],
            'guidance' => [
                'class' => 'common\behaviors\GUIDFieldBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['guid'],
                ],
                'preserveNonEmptyValues' => true,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением объекта

            // удаляем банковские счета контрагента
            foreach (CompaniesBankAccounts::find()->where(['company_id' => $this->id])->all() as $record) $record->delete();

            return true;
        }

        return false;
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getPos()->count() > 0) return true;

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'created_by'])->from(['createdProfile' => 'profile']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return !empty($this->createdByProfile) ? (!empty($this->createdByProfile->name) ? $this->createdByProfile->name : $this->createdBy->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompaniesBankAccounts()
    {
        return $this->hasMany(CompaniesBankAccounts::class, ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPos()
    {
        return $this->hasMany(Po::class, ['company_id' => 'id']);
    }
}
