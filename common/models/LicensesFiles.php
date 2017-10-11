<?php

namespace common\models;

use Yii;
use dektrium\user\models\Profile;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "licenses_files".
 *
 * @property integer $id
 * @property integer $uploaded_at
 * @property integer $uploaded_by
 * @property integer $organization_id
 * @property string $ffp
 * @property string $fn
 * @property string $ofn
 * @property integer $size
 *
 * @property integer $uploadedByProfile
 *
 * @property Organizations $organization
 * @property User $uploadedBy
 * @property LicensesFkkoPages[] $licensesFkkoPages
 */
class LicensesFiles extends \yii\db\ActiveRecord
{
    /**
     * @var string коды ФККО в текстовом виде
     */
    public $fkkosTextarea;

    /**
     * @var array табличная часть кодов ФККО, массив моделей
     */
    public $tpFkkos;

    /**
     * @var \yii\web\UploadedFile
     */
    public $importFile;

    /**
     * @var string для вывода в списке сканов отдельной колонки "Коды ФККО"
     */
    public $fkkos;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'licenses_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['organization_id'], 'required'],
            [['uploaded_at', 'uploaded_by', 'organization_id', 'size'], 'integer'],
            [['ffp', 'fn', 'ofn'], 'string', 'max' => 255],
            [['fkkosTextarea', 'tpFkkos'], 'safe'],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['uploaded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uploaded_by' => 'id']],
            [['importFile'], 'file', 'skipOnEmpty' => false],
            // собственные правила валидации
            ['fkkosTextarea', 'validateFkkos', 'skipOnEmpty' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uploaded_at' => 'Дата и время загрузки',
            'uploaded_by' => 'Автор загрузки',
            'organization_id' => 'Организация',
            'ffp' => 'Полный путь к файлу',
            'fn' => 'Имя файла',
            'ofn' => 'Оригинальное имя файла',
            'size' => 'Размер файла',
            'importFile' => 'Файл',
            'fkkosTextarea' => 'Коды ФККО',
            'fkkos' => 'Коды ФККО',
            // вычисляемые поля
            'uploadedByName' => 'Автор загрузки',
            'organizationName' => 'Организация',
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
                    ActiveRecord::EVENT_BEFORE_INSERT => ['uploaded_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['uploaded_by'],
                ],
            ],
        ];
    }

    /**
     * Перед удалением записи из базы, удалим файл физически с диска.
     * @return bool
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            LicensesFkkoPages::deleteAll(['file_id' => $this->id]);

            if (file_exists($this->ffp)) unlink($this->ffp);

            return true;
        }
        else return false;
    }

    /**
     * @inheritdoc
     */
    public function validateFkkos()
    {
        // все строки, которые ввел пользователь переводим в массив
        $array = explode("\n", $this->fkkosTextarea);
        if (count($array) > 0) {
            $this->tpFkkos = [];
            foreach ($array as $fkko) {
                $fkko = trim(str_replace(' ', '', $fkko));
                if ($fkko == null) continue;

                // по очереди проверяем существование каждого кода ФККО
                $model = Fkko::findOne(['fkko_code' => intval($fkko)]);

                if ($model != null) {
                    $fkkoPages = new LicensesFkkoPages([
                        'fkko_id' => $model->id,
                    ]);
                    $this->tpFkkos[] = $fkkoPages;
                }
                else
                    $this->addError('tpFkkos', 'Код ФККО ' . $fkko . ' не обнаружен!');
            }
        }
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getLicensesFkkoPages()->count() > 0) return true;

        return false;
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить загружаемые пользователем файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @return bool|string
     */
    public static function getUploadsFilepath()
    {
        $filepath = Yii::getAlias('@uploads-licenses-files-fs');
        if (!is_dir($filepath)) {
            if (!FileHelper::createDirectory($filepath)) return false;
        }

        return realpath($filepath);
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function upload($filename)
    {
        if ($this->validate()) {
            $this->importFile->saveAs($filename);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::className(), ['id' => 'organization_id']);
    }

    /**
     * Возвращает наименование организации.
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organization != null ? $this->organization->name_short : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'uploaded_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'uploaded_by']);
    }

    /**
     * Возвращает имя автора загрузки файла.
     * @return string
     */
    public function getUploadedByName()
    {
        return $this->uploaded_by == null ? '' : ($this->uploadedByProfile == null ? $this->uploadedBy->username : $this->uploadedByProfile->name);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicensesFkkoPages()
    {
        return $this->hasMany(LicensesFkkoPages::className(), ['file_id' => 'id']);
    }
}
