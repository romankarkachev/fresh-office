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
 * @property integer $fo_project
 * @property integer $fo_customer
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
            [['created_at', 'author_id', 'fo_project', 'fo_customer'], 'integer'],
            [['doc_date', 'hks'], 'safe'],
            [['comment'], 'string'],
            [['doc_num'], 'string', 'max' => 20],
            [['fo_contract'], 'string', 'max' => 100],
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
            'fo_contract' => 'Договор из Fresh Office',
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
     * Выполняет выборку данных проектов с идентификатором, переданным в параметрах.
     * @param integer $project_id идентификатор проекта в MS SQL
     * @return mixed
     */
    public static function makeDirectSQL_ProjectData($project_id)
    {
        $query_text = '
SELECT
    LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY,
    LIST_PROJECT_COMPANY.ID_COMPANY,
    DATE_CREATE_PROGECT,
    ADD_numb_dogovor AS contract_basic, ADD_date_finish AS contract_date,
    COMPANY.COMPANY_NAME AS company_name
FROM LIST_PROJECT_COMPANY
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_PROJECT_COMPANY.ID_COMPANY
WHERE ID_LIST_PROJECT_COMPANY = ' . $project_id;
        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Выполняет выборку данных табличной части проекта с идентификатором, переданным в параметрах.
     * @param integer $project_id идентификатор проекта в MS SQL
     * @return mixed
     */
    public static function makeDirectSQL_ProjectTablePart($project_id)
    {
        // устаревший запрос, он берет данные табличной части из проекта, а не из документа Счет
        // SELECT
        //     ID_TOVAR, DISCRIPTION_TOVAT AS DESCRIPTION_TOVAR, KOLVO
        // FROM LIST_TOVAR_PROJECT
        // WHERE ID_LIST_PROJECT_COMPANY = 

        // запрос табличных частей документов с типом 16 (Счет)
        $query_text = '
SELECT LIST_TOVAR_DOC.ID_TOVAR, LIST_TOVAR_DOC.TOVAR_DOC AS DESCRIPTION_TOVAR, LIST_TOVAR_DOC.KOL_VO AS KOLVO, LIST_TOVAR_DOC.ED_IZM_TOVAR
FROM CBaseCRM_Fresh_7x.dbo.LIST_TOVAR_DOC
WHERE LIST_TOVAR_DOC.ID_DOC IN (
    SELECT TOP 1 ID_DOC FROM LIST_DOCUMENTS
    WHERE LIST_DOCUMENTS.ID_LIST_PROJECT_COMPANY = '  . $project_id . ' AND LIST_DOCUMENTS.ID_TIP_DOC = 16
    ORDER BY LIST_DOCUMENTS.DATA_DOC DESC
)';
        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
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
