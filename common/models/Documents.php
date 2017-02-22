<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use dektrium\user\models\Profile;

/**
 * This is the model class for table "documents".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $author_id
 * @property string $doc_num
 * @property string $doc_date
 * @property string $fo_project
 * @property string $fo_customer
 * @property string $fo_contract
 * @property string $comment
 *
 * @property integer $documentsTpsCount
 * @property array $documentsHksIdsArray
 *
 * @property User $author
 * @property DocumentsHk[] $documentsHks
 * @property DocumentsTp[] $documentsTps
 */
class Documents extends \yii\db\ActiveRecord
{
    /**
     * Табличая часть документа.
     * @var array
     */
    public $tp;

    /**
     * Виды обращения с отходами.
     * @var array
     */
    public $hks;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'documents';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['author_id'], 'required'],
            [['created_at', 'author_id', 'fo_project', 'fo_customer', 'fo_contract'], 'integer'],
            [['doc_date', 'hks'], 'safe'],
            [['comment'], 'string'],
            [['doc_num'], 'string', 'max' => 20],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            // собственные правила валидации
            ['tp', 'validateTablePart'],
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
            'author_id' => 'Автор создания записи',
            'doc_num' => 'Номер документа',
            'doc_date' => 'Дата документа',
            'fo_project' => 'ID проекта во Fresh Office',
            'fo_customer' => 'ID заказчика во Fresh Office',
            'fo_contract' => 'ID договора во Fresh Office',
            'comment' => 'Примечание',
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
        ];
    }

    /**
     * Собственное правило валидации для табличной части документа.
     */
    public function validateTablePart()
    {
        if (count($this->tp) > 0) {
            $row_numbers = [];
            foreach ($this->tp as $index => $item) {
                $oa = new DocumentsTp();
                $oa->attributes  = $item;
                if (!$oa->validate(['product_id'])) {
                    $row_numbers[] = ($index+1);
                }
            }
            if (count($row_numbers) > 0) $this->addError('tp', 'Не все обязательные поля в табличной части заполнены! Строки: '.implode(',', $row_numbers).'.');
        }
    }

    /**
     * Выполняет GET-запрос с базовой аутентификацией для получения данных по API FreshOffice.
     * @param string $entity
     * @param string $select
     * @param string $filter
     * @param string $expand
     * @return mixed
     */
    public static function makeGetRequestToApi($entity, $select = null, $filter = null, $expand = null)
    {
        $api_id = 1311;
        $api_password = 'peGzgff0wMPElm5osVy8vCWgCXzpr5Ir';
        $api_url = 'https://api.myfreshcloud.com/' . $entity . '/';
        $auth_key = base64_encode($api_id.':'.$api_password);

        $data = [];
        if ($filter != null) $data['$filter'] = $filter;
        if ($select != null) $data['$select'] = $select;
        if (sizeof($data)) {
            $api_url .= '?'.http_build_query($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic '.$auth_key,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Превращает данные из массива идентификаторов в массив моделей DocumentsTp.
     * @return array
     */
    public function makeTpModelsFromPostArray()
    {
        $result = [];
        if (is_array($this->tp)) if (count($this->tp) > 0) {
            foreach ($this->tp as $item)
            {
                $dtp = new DocumentsTp();
                $dtp->attributes = $item;
                $dtp->doc_id = $this->id;
                $dtp->author_id = Yii::$app->user->id;
                $result[] = $dtp;
            }
            return $result;
        }
    }

    /**
     * Превращает данные из массива идентификаторов в массив моделей DocumentsHk.
     * @return array
     */
    public function makeHkModelsFromPostArray()
    {
        $result = [];
        if (is_array($this->hks)) if (count($this->hks) > 0) {
            foreach ($this->hks as $item)
            {
                $dhk = new DocumentsHk();
                $dhk->attributes = $item;
                $dhk->doc_id = $this->id;
                $result[] = $dhk;
            }
            return $result;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthorProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentsHks()
    {
        return $this->hasMany(DocumentsHk::className(), ['doc_id' => 'id']);
    }

    /**
     * Возвращает массив идентификаторов видов обращения с отходами документа.
     * @return array
     */
    public function getDocumentsHksIdsArray()
    {
        return $this->hasMany(DocumentsHk::className(), ['doc_id' => 'id'])->select('hk_id')->asArray()->column();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentsTps()
    {
        return $this->hasMany(DocumentsTp::className(), ['doc_id' => 'id']);
    }

    /**
     * Возвращает количество строк табличной части
     * @return integer
     */
    public function getDocumentsTpsCount()
    {
        return $this->hasMany(DocumentsTp::className(), ['doc_id' => 'id'])->count();
    }
}
