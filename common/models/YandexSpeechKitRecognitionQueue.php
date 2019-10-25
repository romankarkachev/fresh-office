<?php

namespace common\models;

use Yii;

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
    const URL_ROOT = 'pbx-calls';

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
            [['created_at', 'call_id'], 'required'],
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
        ];
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
