<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use common\behaviors\IndexFieldBehavior;

/**
 * This is the model class for table "transport".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property integer $ferryman_id
 * @property integer $is_deleted
 * @property integer $state_id
 * @property integer $tt_id
 * @property integer $brand_id
 * @property string $vin
 * @property string $vin_index
 * @property string $rn
 * @property string $rn_index
 * @property string $trailer_rn
 * @property string $osago_expires_at
 * @property integer $is_dopog
 * @property string $comment
 *
 * @property string $brandName
 * @property string $ttName
 * @property integer $inspCount
 *
 * @property User $updatedBy
 * @property User $createdBy
 * @property TransportBrands $brand
 * @property Ferrymen $ferryman
 * @property TransportTypes $tt
 * @property TransportFiles[] $transportFiles
 * @property TransportInspections[] $transportInspections
 */
class Transport extends \yii\db\ActiveRecord
{
    /**
     * @var array набор доступных транспортному средству типов погрузки
     */
    public $loadTypes;

    /*
     * @var UploadedFile ОСАГО
     */
    public $fileOsago;

    /*
     * @var UploadedFile лицевая сторона ПТС
     */
    public $filePtsFace;

    /*
     * @var UploadedFile оборотная сторона ПТС
     */
    public $filePtsReverse;

    /*
     * @var UploadedFile лицевая сторона СТС
     */
    public $fileStsFace;

    /*
     * @var UploadedFile оборотная сторона СТС
     */
    public $fileStsReverse;

    /*
     * @var UploadedFile диагностическая карта
     */
    public $fileDk;

    /*
     * @var UploadedFile фото автомобиля
     */
    public $fileAutoPicture;

    /**
     * Вычисляемое виртуальное поле.
     * @var integer количество техосмотров транспортного средства
     */
    public $inspCount;

    /**
     * Вычисляемое виртуальное поле.
     * @var integer техосмотры транспортного средства в строку через запятую
     */
    public $inspDetails;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id', 'brand_id'], 'required'],
            [['osago_expires_at', 'loadTypes'], 'safe'],
            [['created_at', 'created_by', 'updated_at', 'updated_by', 'ferryman_id', 'is_deleted', 'state_id', 'tt_id', 'brand_id', 'is_dopog'], 'integer'],
            [['comment'], 'string'],
            [['vin', 'vin_index'], 'string', 'max' => 50],
            [['rn', 'rn_index', 'trailer_rn'], 'string', 'max' => 30],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportBrands::className(), 'targetAttribute' => ['brand_id' => 'id']],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
            [['tt_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransportTypes::className(), 'targetAttribute' => ['tt_id' => 'id']],
            [['fileOsago'], 'file', 'skipOnEmpty' => true],
            [['filePtsFace'], 'file', 'skipOnEmpty' => true],
            [['filePtsReverse'], 'file', 'skipOnEmpty' => true],
            [['fileStsFace'], 'file', 'skipOnEmpty' => true],
            [['fileStsReverse'], 'file', 'skipOnEmpty' => true],
            [['fileDk'], 'file', 'skipOnEmpty' => true],
            [['fileAutoPicture'], 'file', 'skipOnEmpty' => true],
            // собственные правила валидации
            ['vin', 'validateVin'],
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
            'ferryman_id' => 'Перевозчик',
            'is_deleted' => 'Признак удаления записи', // 0 - пометка не установлена, 1 - запись помечена на удаление
            'state_id' => 'Статус', // 1 - нареканий нет, 2 - есть замечания, 3 - черный список
            'tt_id' => 'Тип',
            'brand_id' => 'Марка',
            'vin' => 'VIN',
            'rn' => 'Госномер',
            'trailer_rn' => 'Прицеп',
            'osago_expires_at' => 'Срок действия полиса ОСАГО',
            'is_dopog' => 'ДОПОГ (допуск на перевозку опасных грузов)',
            'comment' => 'Примечание',
            'fileOsago' => 'Файл с изображением полиса ОСАГО',
            'filePtsFace' => 'Файл с изображением лицевой стороны ПТС',
            'filePtsReverse' => 'Файл с изображением оборотной стороны ПТС',
            'fileStsFace' => 'Файл с изображением лицевой стороны СТС',
            'fileStsReverse' => 'Файл с изображением оборотной стороны СТС',
            'fileDk' => 'Файл с изображением диагностической карты',
            'fileAutoPicture' => 'Файл с изображением автомобиля',
            'loadTypes' => 'Способы погрузки, доступные транспортному средству',
            // вычисляемые поля
            'ferrymanName' => 'Перевозчик',
            'stateName' => 'Статус',
            'ttName' => 'Тип',
            'brandName' => 'Марка',
            'inspCount' => 'Техосмотров',
            'inspDetails' => 'Техосмотры',
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
            'indexVinField' => [
                'class' => 'common\behaviors\IndexFieldBehavior',
                'in_attribute' => 'vin',
                'out_attribute' => 'vin_index',
            ],
            'indexRnField' => [
                'class' => 'common\behaviors\IndexFieldBehavior',
                'in_attribute' => 'rn',
                'out_attribute' => 'rn_index',
            ]
        ];
    }

    /**
     * Собственное правило валидации для VIN-номера транспортного средства.
     */
    public function validateVin()
    {
        $query = self::find()->where(['vin_index' => IndexFieldBehavior::processValue($this->vin)]);
        if ($this->id != null) $query->andWhere(['not in', 'id', $this->id]);
        if ($query->count() > 0)
            $this->addError('vin', 'Автомобиль с таким VIN уже существует.');
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением документа

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = TransportFiles::find()->where(['transport_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            // удаляем техсмотры
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $tis = TransportInspections::find()->where(['transport_id' => $this->id])->all();
            foreach ($tis as $ti) $ti->delete();

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
            // поля "Дата и время-" и "Автор изменений" перевозчика, к которому относится ТС, должны быть обновлены
            $this->ferryman->updated_at = time();
            $this->ferryman->updated_by = Yii::$app->user->id;
            $this->ferryman->save(false);
        }
    }

    /**
     * @param string $attribute наименование атрибута ($fileOsago, $filePtsFace, $filePtsReverse и т.д.)
     * @param string $ofn оригинальное имя файла
     * @param integer $ufm_id тип контента файла (вид документа)
     * @return bool
     */
    public function upload($attribute, $ofn, $ufm_id)
    {
        if ($this->validate()) {
            $uploadDir = TransportFiles::getUploadsFilepath();
            if (!file_exists($uploadDir) && !is_dir($uploadDir)) mkdir($uploadDir, 0755);

            $fn = strtolower(Yii::$app->security->generateRandomString() . '.' . $this->{$attribute}->extension);
            $ffp = $uploadDir . '/' . $fn;
            if ($this->{$attribute}->saveAs($ffp)) {
                $fu = TransportFiles::findOne(['transport_id' => $this->id, 'ufm_id' => $ufm_id]);
                if ($fu == null)
                    $fu = new TransportFiles([
                        'transport_id' => $this->id,
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
     * Возвращает представление транспортного средства для вывода на экране.
     * @return string
     */
    public function getRepresentation()
    {
        $result = $this->ttName . ' ' . $this->brandName;
        $result = trim($result);
        $result .= ' г/н ' . $this->rn;
        $result = trim($result);

        return $result;
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
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::className(), ['id' => 'ferryman_id']);
    }

    /**
     * Возвращает наименование перевозчка.
     * @return string
     */
    public function getFerrymanName()
    {
        return $this->ferryman != null ? $this->ferryman->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTt()
    {
        return $this->hasOne(TransportTypes::className(), ['id' => 'tt_id']);
    }

    /**
     * Возвращает наименование типа транспортного средства.
     * @return string
     */
    public function getTtName()
    {
        return $this->tt != null ? $this->tt->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(TransportBrands::className(), ['id' => 'brand_id']);
    }

    /**
     * Возвращает наименование марки автомобиля.
     * @return string
     */
    public function getBrandName()
    {
        return $this->brand != null ? $this->brand->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportFiles()
    {
        return $this->hasMany(TransportFiles::className(), ['transport_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportInspections()
    {
        return $this->hasMany(TransportInspections::className(), ['transport_id' => 'id']);
    }
}
