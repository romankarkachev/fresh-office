<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "documents".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $author_id Автор создания записи
 * @property string $doc_num Номер документа
 * @property string $doc_date Дата документа
 * @property string $act_date Дата акта утилизации
 * @property int $org_id Организация
 * @property int $fo_project ID проекта во Fresh Office
 * @property int $fo_customer ID заказчика во Fresh Office
 * @property string $fo_contract Договор из Fresh Office
 * @property int $ed_id Электронный документ
 * @property string $comment Примечание
 *
 * @property string $createdByProfileName
 * @property integer $documentsTpsCount
 * @property array $documentsHksIdsArray
 * @property string $organizationName
 * @property string $counteragentName
 * @property string $edRep
 *
 * @property User $author
 * @property Profile $createdByProfile
 * @property Organizations $organization
 * @property foCompany $counteragent
 * @property Edf $ed
 * @property DocumentsHk[] $documentsHks
 * @property DocumentsTp[] $documentsTps
 */
class Documents extends \yii\db\ActiveRecord
{
    /**
     * @var integer количество строк табличной части (виртуальное присоединяемое поле)
     */
    public $tpCount;

    /**
     * @var array табличая часть документа
     */
    public $tp;

    /**
     * @var array виды обращения с отходами
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
            [['created_at', 'author_id', 'org_id', 'fo_project', 'fo_customer', 'ed_id'], 'integer'],
            [['doc_date', 'hks', 'act_date'], 'safe'],
            [['comment'], 'string'],
            [['doc_num'], 'string', 'max' => 20],
            [['fo_contract'], 'string', 'max' => 100],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['author_id' => 'id']],
            [['ed_id'], 'exist', 'skipOnError' => true, 'targetClass' => Edf::class, 'targetAttribute' => ['ed_id' => 'id']],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['org_id' => 'id']],
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
            'act_date' => 'Дата акта утилизации',
            'org_id' => 'Организация',
            'fo_project' => 'ID проекта во Fresh Office',
            'fo_customer' => 'ID заказчика во Fresh Office',
            'fo_contract' => 'Договор из Fresh Office',
            'ed_id' => 'Электронный документ',
            'comment' => 'Примечание',
            // виртуальные поля
            'tpCount' => 'К-во строк',
            // вычисляемые поля
            'createdByProfileName' => 'Автор',
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
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['author_id'],
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

            // удаляем табличную часть
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $records = DocumentsTp::find()->where(['doc_id' => $this->id])->all();
            foreach ($records as $record) $record->delete();

            // удаляем виды обращения с отходами
            DocumentsHk::deleteAll(['doc_id' => $this->id]);

            return true;
        }

        return false;
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
    LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT AS state_id, LIST_SPR_PRIZNAK_PROJECT.PRIZNAK_PROJECT AS state_name,
    LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT AS type_id, LIST_SPR_PROJECT.NAME_PROJECT AS type_name,
    LIST_PROJECT_COMPANY.ID_COMPANY,
    DATE_CREATE_PROGECT,
    ADD_numb_dogovor AS contract_basic, ADD_date_finish AS contract_date,
    COMPANY.COMPANY_NAME AS company_name
FROM LIST_PROJECT_COMPANY
LEFT JOIN LIST_SPR_PROJECT ON LIST_SPR_PROJECT.ID_LIST_SPR_PROJECT = LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT
LEFT JOIN LIST_SPR_PRIZNAK_PROJECT ON LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT = LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT
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
    WHERE LIST_DOCUMENTS.ID_LIST_PROJECT_COMPANY = '  . $project_id . ' AND LIST_DOCUMENTS.ID_TIP_DOC = 30
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
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'author_id']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return $this->createdByProfile != null ? ($this->createdByProfile->name != null ? $this->createdByProfile->name : $this->author->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'org_id']);
    }

    /**
     * Возвращает наименование организации.
     * @return string
     */
    public function getOrganizationName()
    {
        return !empty($this->organization) ? $this->organization->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounteragent()
    {
        return $this->hasOne(foCompany::class, ['ID_COMPANY' => 'fo_customer']);
    }

    /**
     * Возвращает наименование контрагента.
     * @return string
     */
    public function getCounteragentName()
    {
        return !empty($this->counteragent) ? trim($this->counteragent->COMPANY_NAME) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEd()
    {
        return $this->hasOne(Edf::class, ['id' => 'ed_id']);
    }

    /**
     * Возвращает представление договора.
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getEdRep()
    {
        return !empty($this->ed) ? '№ ' . $this->ed->doc_num . ' от ' . Yii::$app->formatter->asDate($this->ed->doc_date, 'php:d.m.Y г.') : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentsHks()
    {
        return $this->hasMany(DocumentsHk::class, ['doc_id' => 'id']);
    }

    /**
     * Возвращает массив идентификаторов видов обращения с отходами документа.
     * @return array
     */
    public function getDocumentsHksIdsArray()
    {
        return $this->hasMany(DocumentsHk::class, ['doc_id' => 'id'])->select('hk_id')->asArray()->column();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentsTps()
    {
        return $this->hasMany(DocumentsTp::class, ['doc_id' => 'id'])->joinWith(['unit', 'hk', 'dc', 'fkko']);
    }

    /**
     * Возвращает количество строк табличной части
     * @return integer
     */
    public function getDocumentsTpsCount()
    {
        return $this->hasMany(DocumentsTp::class, ['doc_id' => 'id'])->count();
    }
}
