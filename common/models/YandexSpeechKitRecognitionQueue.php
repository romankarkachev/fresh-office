<?php

namespace common\models;

use backend\controllers\PbxCallsController;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "yskrq".
 *
 * @property int $id
 * @property int $created_at Дата и время постановки в очередь
 * @property int $created_by Пользователь, который отправил файл на распознавание
 * @property int $check_after Дата и время, раньше которых распознавание не может быть завершено (проверять не ранее этой даты)
 * @property int $call_id Звонок
 * @property string $url_bucket Ссылка на файл, размещенный в бакете
 * @property string $operation_id Идентификатор операции для проверки готовности распознавания
 *
 * @property string $createdByProfileName
 *
 * @property User $createdBy
 * @property Profile $createdByProfile
 */
class YandexSpeechKitRecognitionQueue extends \yii\db\ActiveRecord
{
    /**
     * URL, применяемый для просмотра списка записей, сортировки и постраничного перехода
     */
    const URL_ROOT = 'recognition-queue';

    /**
     * URL, ведущий в список записей без отбора
     */
    const URL_ROOT_AS_ARRAY = ['/' . self::URL_ROOT];

    /**
     * Заголовок страницы
     */
    const PAGE_TITLE = 'Список аудиодорожек, находящихся на распознавании в Yandex SpeechKit';

    /**
     * Текст в строке хлебных крошек
     */
    const BREADCRUMBS_TITLE = 'Очередь распознавания';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'yskrq';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['call_id'], 'required'],
            [['created_at', 'created_by', 'check_after', 'call_id'], 'integer'],
            [['url_bucket'], 'string', 'max' => 255],
            [['operation_id'], 'string', 'max' => 30],
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
            'created_at' => 'Дата и время постановки в очередь',
            'created_by' => 'Пользователь, который отправил файл на распознавание',
            'check_after' => 'Дата и время, раньше которых распознавание не может быть завершено (проверять не ранее этой даты)',
            'call_id' => 'Звонок',
            'url_bucket' => 'Ссылка на файл, размещенный в бакете',
            'operation_id' => 'Идентификатор операции для проверки готовности распознавания',
            // вычисляемые поля
            'createdByProfileName' => 'Отправитель',
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
                'preserveNonEmptyValues' => true,
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
                'preserveNonEmptyValues' => true,
            ],
        ];
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить распознанные Яндексом файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @param $model PbxCalls
     * @return bool|string
     * @throws \yii\base\Exception
     */
    public static function getUploadsFilepath($model)
    {
        $created_at = strtotime($model->calldate);
        $filepath = Yii::getAlias('@uploads-yrr-fs') . '/' . date('Y', $created_at) . '/' . date('m', $created_at) . '/' . date('d', $created_at);
        if (!is_dir($filepath)) {
            if (!\yii\helpers\FileHelper::createDirectory($filepath, 0775, true)) return false;
        }

        return realpath($filepath);
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
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by'])->from(['createdByProfile' => 'profile']);
    }

    /**
     * Возвращает имя пользователя, поставившего аудиодорожку на распознавание.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return !empty($this->createdByProfile) ? (!empty($this->createdByProfile->name) ? $this->createdByProfile->name : $this->createdBy->username) : '';
    }
}
