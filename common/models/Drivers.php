<?php

namespace common\models;

use Yii;
use common\behaviors\IndexFieldBehavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * This is the model class for table "drivers".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property integer $user_id
 * @property integer $ferryman_id
 * @property integer $is_deleted
 * @property integer $state_id
 * @property string $surname
 * @property string $name
 * @property string $patronymic
 * @property string $driver_license
 * @property string $dl_issued_at
 * @property string $driver_license_index
 * @property string $phone
 * @property string $phone2
 * @property string $pass_serie
 * @property string $pass_num
 * @property string $pass_issued_at
 * @property string $pass_issued_by
 * @property integer $has_smartphone
 *
 * @property string $ferrymanName
 * @property integer $instrCount
 *
 * @property User $user
 * @property User $createdBy
 * @property Ferrymen $ferryman
 * @property User $updatedBy
 * @property DriversFiles[] $driversFiles
 * @property DriversInstructings[] $driversInstructings
 */
class Drivers extends \yii\db\ActiveRecord
{
    /**
     * Набор имен реквизитов, которые должны обрабатываться как номера телефонов
     */
    const PHONE_ATTRIBUTES = ['phone', 'phone2'];

    /**
     * Префикс для имен пользователей. Добавляется в начало имени при создании пользователя из карточки водителя.
     */
    const FOREIGN_DRIVER_LOGIN_PREFIX = 'foreignDriver';

    /**
     * Наименование роли водителей перевозчиков
     */
    const FOREIGN_DRIVER_ROLE_NAME = self::FOREIGN_DRIVER_LOGIN_PREFIX;

    /**
     * @var UploadedFile лицевая сторона водительского удостоверения
     */
    public $fileDlFace;

    /**
     * @var UploadedFile оборотная сторона водительского удостоверения
     */
    public $fileDlReverse;

    /**
     * @var UploadedFile главный разворот паспорта
     */
    public $filePassportFace;

    /**
     * @var UploadedFile происка в паспорте
     */
    public $filePassportReverse;

    /**
     * Вычисляемое виртуальное поле.
     * @var integer количество инструктажей водителя
     */
    public $instrCount;

    /**
     * Вычисляемое виртуальное поле.
     * @var integer инструктажи водителя в строку через запятую
     */
    public $instrDetails;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'drivers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id', 'surname', 'name', 'driver_license', 'phone'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by', 'user_id', 'ferryman_id', 'is_deleted', 'state_id', 'has_smartphone'], 'integer'],
            [['dl_issued_at', 'pass_issued_at'], 'safe'],
            [['surname', 'name', 'patronymic'], 'string', 'max' => 50],
            [['driver_license', 'driver_license_index'], 'string', 'max' => 30],
            [['phone', 'phone2'], 'string', 'min' => 15],
            [['pass_serie', 'pass_num'], 'string', 'max' => 10],
            [['pass_issued_by'], 'string', 'max' => 150],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['fileDlFace'], 'file', 'skipOnEmpty' => true],
            [['fileDlReverse'], 'file', 'skipOnEmpty' => true],
            [['filePassportFace'], 'file', 'skipOnEmpty' => true],
            [['filePassportReverse'], 'file', 'skipOnEmpty' => true],
            [['patronymic', 'pass_serie', 'pass_num'], 'default', 'value' => null],
            // собственные правила валидации
            [['phone', 'phone2'], 'validatePhones'],
            ['driver_license', 'validateDriverLicense'],
            //[['surname', 'name', 'patronymic', 'driver_license', 'pass_serie', 'pass_num'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['surname', 'name', 'patronymic', 'driver_license', 'pass_serie', 'pass_num'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'updated_at' => 'Дата и время изменения',
            'updated_by' => 'Автор изменений',
            'user_id' => 'Сопоставленный пользователь системы (для авторизации в мобильном приложении)',
            'ferryman_id' => 'Перевозчик',
            'is_deleted' => 'Признак удаления записи', // 0 - пометка не установлена, 1 - запись помечена на удаление
            'state_id' => 'Статус', // 1 - нареканий нет, 2 - есть замечания, 3 - черный список
            'surname' => 'Фамилия',
            'name' => 'Имя',
            'patronymic' => 'Отчество',
            'driver_license' => 'Водительское удостоверение',
            'dl_issued_at' => 'ВУ выдано', // Дата выдачи водительского удостоверения
            'phone' => 'Телефон',
            'phone2' => 'Другой телефон',
            'pass_serie' => 'Серия',
            'pass_num' => 'Номер',
            'pass_issued_at' => 'Дата выдачи',
            'pass_issued_by' => 'Кем выдан',
            'has_smartphone' => 'Смартфон с камерой',
            'fileDlFace' => 'Файл с изображением лицевой стороны водительского удостоверения',
            'fileDlReverse' => 'Файл с изображением оборотной стороны водительского удостоверения',
            'filePassportFace' => 'Файл с главным разворотом паспорта',
            'filePassportReverse' => 'Файл с пропиской в паспорте',
            // вычисляемые поля
            'ferrymanName' => 'Перевозчик',
            'stateName' => 'Статус',
            'instrCount' => 'Инструктажей',
            'instrDetails' => 'Инструктажи',
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
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_by'],
                ],
            ],
            'indexField' => [
                'class' => 'common\behaviors\IndexFieldBehavior',
                'in_attribute' => 'driver_license',
                'out_attribute' => 'driver_license_index',
            ]
        ];
    }

    /**
     * Собственное правило валидации для номера телефона.
     */
    public function validatePhones()
    {
        foreach (self::PHONE_ATTRIBUTES as $attribute) {
            $phone_processed = self::leaveOnlyDigits($this->{$attribute});
            if (strlen($phone_processed) < 10 && !empty($phone_processed)) $this->addError($attribute, 'Номер телефона должен состоять из 10 цифр.');
        }
    }

    /**
     * Функция преобразует входящую строку, возвращая только цифры.
     * @param $value string
     * @return string
     */
    public static function leaveOnlyDigits($value)
    {
        return preg_replace("/[^0-9]/", '', $value);
    }

    /**
     * Собственное правило валидации для номера водительского удостоверения.
     */
    public function validateDriverLicense()
    {
        $query = self::find()->where(['driver_license_index' => IndexFieldBehavior::processValue($this->driver_license)]);
        if ($this->id != null) $query->andWhere(['not in', 'id', $this->id]);
        if ($query->count() > 0)
            $this->addError('driver_license', 'Водитель с таким удостоверением уже существует.');
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением объекта

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = DriversFiles::find()->where(['driver_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            // удаляем инструктажи
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $dis = DriversInstructings::find()->where(['driver_id' => $this->id])->all();
            foreach ($dis as $di) $di->delete();

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // убираем из номера телефона все символы кроме цифр
            foreach (self::PHONE_ATTRIBUTES as $attribute) {
                $this->$attribute = self::leaveOnlyDigits($this->$attribute);
                if (empty($this->$attribute)) $this->$attribute = null;
            }

            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        if (!$insert) {
            // поля "Дата и время-" и "Автор изменений" перевозчика, к которому относится водитель, должны быть обновлены
            $this->ferryman->updated_at = time();
            $this->ferryman->updated_by = Yii::$app->user->id;
            $this->ferryman->save(false);
        }
    }

    /**
     * Выполняет преобразование номера телефона к удобному для восприятия виду.
     * @param $phone string номер телефона (оригинал)
     * @return string
     */
    public static function normalizePhoneNumber($phone)
    {
        if ($phone != null && $phone != '')
            if (preg_match('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', $phone, $matches))
                return '+7 ('.$matches[1].') '.$matches[2].'-'.$matches[3].'-'.$matches[4];

        // не удалось преобразовать в человеческий вид - отображаем как есть
        return $phone;
    }

    /**
     * @param string $attribute наименование атрибута ($fileDlFace, $fileDlReverse и т.д.)
     * @param string $ofn оригинальное имя файла
     * @param integer $ufm_id тип контента файла (вид документа)
     * @return bool
     */
    public function upload($attribute, $ofn, $ufm_id)
    {
        if ($this->validate()) {
            $uploadDir = DriversFiles::getUploadsFilepath();
            if (!file_exists($uploadDir) && !is_dir($uploadDir)) mkdir($uploadDir, 0755);

            $fn = strtolower(Yii::$app->security->generateRandomString() . '.' . $this->{$attribute}->extension);
            $ffp = $uploadDir . '/' . $fn;
            if ($this->{$attribute}->saveAs($ffp)) {
                $fu = DriversFiles::findOne(['driver_id' => $this->id, 'ufm_id' => $ufm_id]);
                if ($fu == null)
                    $fu = new DriversFiles([
                        'driver_id' => $this->id,
                        'ufm_id' => $ufm_id,
                    ]);
                else unlink($fu->ffp);

                $fu->ffp = $ffp;
                $fu->fn = $fn;
                $fu->ofn = $ofn;
                $fu->size = filesize($ffp);
                if ($fu->save())
                    return true;
                else
                    // удаляем загруженный файл, возвратится false
                    unlink($ffp);
            }
        }

        return false;
    }

    /**
     * Возвращает наименование статуса.
     * @return string
     */
    public function getStateName()
    {
        if (null === $this->state_id) {
            return '<не определен>';
        }

        $sourceTable = Ferrymen::fetchStates();
        $key = array_search($this->state_id, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::className(), ['id' => 'ferryman_id']);
    }

    /**
     * Возвращает наименование перевозчика.
     * @return string
     */
    public function getFerrymanName()
    {
        return $this->ferryman != null ? $this->ferryman->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDriversFiles()
    {
        return $this->hasMany(DriversFiles::className(), ['driver_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDriversInstructings()
    {
        return $this->hasMany(DriversInstructings::className(), ['driver_id' => 'id']);
    }
}
