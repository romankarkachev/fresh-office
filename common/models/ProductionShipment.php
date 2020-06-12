<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "production_shipment".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property string $rn Госномер
 * @property int $transport_id Транспортное средство
 * @property int $site_id Производственная площадка
 * @property int $fo_project_id Проект из CRM Fresh Office
 * @property string $subject Заголовок письма
 * @property string $comment Комментарий
 *
 * @property string $createdByProfileName
 * @property string $ferrymanName
 * @property string $ferrymanCrmName
 * @property string $transportRep
 * @property string $siteName
 * @property string $shipmentRep
 *
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property Transport $transport
 * @property TransportBrands $brand
 * @property TransportTypes $tt
 * @property Ferrymen $ferryman
 * @property ProductionSites $site
 * @property ProductionShipmentFiles[] $productionShipmentFiles
 */
class ProductionShipment extends ActiveRecord
{
    const LABEL_ROOT = 'Отправка техники';
    const BUTTON_SUBMIT_SEND_NAME = 'commit';

    const URL_UPDATE = 'update';
    const URL_UPDATE_ROUTE = '/' . self::URL_ROOT;
    const URL_UPDATE_ROUTE_AS_ARRAY = [self::URL_UPDATE_ROUTE];

    const URL_ROOT = 'production-shipment';
    const URL_ROOT_ROUTE = '/' . self::URL_ROOT;
    const URL_ROOT_ROUTE_AS_ARRAY = [self::URL_ROOT_ROUTE];

    const URL_IDENTIFY_TRANSPORT = 'identify-transport';
    const URL_IDENTIFY_TRANSPORT_ROUTE = '/' . self::URL_ROOT_ROUTE . '/' . self::URL_IDENTIFY_TRANSPORT;
    const URL_IDENTIFY_TRANSPORT_ROUTE_AS_ARRAY = [self::URL_IDENTIFY_TRANSPORT_ROUTE];

    /**
     * URL для загрузки файлов через ajax
     */
    const URL_UPLOAD_FILES = 'upload-files';
    const URL_UPLOAD_FILES_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_UPLOAD_FILES];

    /**
     * URL для скачивания приаттаченных файлов
     */
    const URL_DOWNLOAD_FILE = 'download-file';

    /**
     * URL для предварительного просмотра файлов через ajax
     */
    const URL_PREVIEW_FILE = 'preview-file';
    const URL_PREVIEW_FILE_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_PREVIEW_FILE];

    /**
     * URL для удаления файла через ajax
     */
    const URL_DELETE_FILE = 'delete-file';
    const URL_DELETE_FILE_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_DELETE_FILE];

    /**
     * URL для рендера файлов отправки
     */
    const URL_RENDER_FILES = 'render-files';
    const URL_RENDER_FILES_AS_ARRAY = ['/' . self::URL_ROOT . '/' . self::URL_RENDER_FILES];

    /**
     * @var array
     */
    public $crudeFiles;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'production_shipment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rn', 'transport_id', 'site_id'], 'required'],
            [['created_at', 'created_by', 'transport_id', 'site_id', 'fo_project_id'], 'integer'],
            [['subject'], 'string', 'max' => 255],
            [['comment'], 'string'],
            [['rn'], 'string', 'max' => 30],
            [['comment'], 'trim'],
            [['comment'], 'default', 'value' => null],
            [['crudeFiles'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 100],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductionSites::class, 'targetAttribute' => ['site_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['transport_id'], 'exist', 'skipOnError' => true, 'targetClass' => Transport::class, 'targetAttribute' => ['transport_id' => 'id']],
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
            'rn' => 'Госномер',
            'transport_id' => 'Транспортное средство',
            'site_id' => 'Производственная площадка',
            'fo_project_id' => 'Проект из CRM Fresh Office',
            'subject' => 'Заголовок письма',
            'comment' => 'Комментарий',
            // виртуальные поля
            'crudeFiles' => 'Файлы',
            // вычисляемые поля
            'createdByProfileName' => 'Автор',
            'transportRep' => 'Транспорт',
            'ferrymanName' => 'Перевозчик',
            'siteName' => 'Площадка',
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
        ];
    }

    /**
     * Выполняет загрузку файлов, создает записи в базе данных о путях к ним.
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function upload()
    {
        $result = [];

        // путь к папке, куда будет загружен файл
        $pifp = ProductionShipmentFiles::getUploadsFilepath($this);
        if (!FileHelper::createDirectory($pifp, 0775, true)) return false;

        foreach ($this->crudeFiles as $file) {
            // имя и полный путь к файлу полноразмерного изображения
            $fn = strtolower(Yii::$app->security->generateRandomString() . '.' . $file->extension);
            $ffp = $pifp . '/' . $fn;

            if ($file->saveAs($ffp)) {
                // заполняем поля записи в базе о загруженном успешно файле
                (new ProductionShipmentFiles([
                    'ps_id' => $this->id,
                    'ffp' => $ffp,
                    'fn' => $fn,
                    'ofn' => $file->name,
                    'size' => filesize($ffp),
                ]))->save() ? $result[] = $ffp : null;
            }
        }

        return $result;
    }

    /**
     * Удаление связанных объектов перед удалением текущего.
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = ProductionShipmentFiles::find()->where(['ps_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            return true;
        }

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
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
    public function getTransport()
    {
        return $this->hasOne(Transport::class, ['id' => 'transport_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(TransportBrands::class, ['id' => 'brand_id'])->via('transport');
    }

    /**
     * Возвращает наименование бренда.
     * @return string
     */
    public function getBrandName()
    {
        return !empty($this->brand) ? $this->brand->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTt()
    {
        return $this->hasOne(TransportTypes::class, ['id' => 'tt_id'])->via('transport');
    }

    /**
     * Возвращает наименование типа транспортного средства.
     * @return string
     */
    public function getTtName()
    {
        return !empty($this->tt) ? $this->tt->name : '';
    }

    /**
     * Возвращает представление транспортного средства.
     * @return string
     */
    public function getTransportRep()
    {
        $result = [];

        if (!empty($this->brand)) {
            $result[] = $this->brand->name;
        }
        if (!empty($this->tt)) {
            $result[] = $this->tt->name;
        }

        if (count($result) > 0) {
            return implode(' ', $result);
        }
        else {
            return 'ТС не обнаружено';
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::class, ['id' => 'ferryman_id'])->via('transport');
    }

    /**
     * Возвращает наименование перевозчика.
     * @return string
     */
    public function getFerrymanName()
    {
        return !empty($this->ferryman) ? $this->ferryman->name : '';
    }

    /**
     * Возвращает наименование перевозчика из CRM.
     * @return string
     */
    public function getFerrymanCrmName()
    {
        return !empty($this->ferryman) ? $this->ferryman->name_crm : '';
    }

    /**
     * Возвращает представление перевозчика и его транспортного средства.
     * @return string
     */
    public function getShipmentRep()
    {
        return (!empty($this->ferryman) && !empty($this->transport)) ? 'Перевозчик: ' . $this->ferryman->name . ', ' . $this->brandName . ' ' . $this->ttName : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(ProductionSites::class, ['id' => 'site_id']);
    }

    /**
     * Возвращает наименование производственной площадки.
     * @return string
     */
    public function getSiteName()
    {
        return !empty($this->site) ? $this->site->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductionShipmentFiles()
    {
        return $this->hasMany(ProductionShipmentFiles::class, ['ps_id' => 'id']);
    }
}
