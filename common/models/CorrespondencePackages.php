<?php

namespace common\models;

use Yii;
use yii\data\ArrayDataProvider;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "correspondence_packages".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $ready_at
 * @property integer $sent_at
 * @property integer $delivered_at
 * @property integer $fo_project_id
 * @property integer $fo_id_company
 * @property string $customer_name
 * @property integer $state_id
 * @property integer $type_id
 * @property string $pad
 * @property integer $pd_id
 * @property string $track_num
 * @property string $other
 * @property string $comment
 *
 * @property string $stateName
 * @property string $typeName
 * @property string $pdName
 *
 * @property PostDeliveryKinds $pd
 * @property ProjectsStates $state
 * @property ProjectsTypes $type
 */
class CorrespondencePackages extends \yii\db\ActiveRecord
{
    /**
     * Табличная часть предоставленных видов документов.
     * @var array
     */
    public $tpPad;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'correspondence_packages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'ready_at', 'sent_at', 'delivered_at', 'fo_project_id', 'fo_id_company', 'state_id', 'type_id', 'pd_id'], 'integer'],
            [['pad', 'other', 'comment'], 'string'],
            [['customer_name'], 'string', 'max' => 255],
            [['track_num'], 'string', 'max' => 50],
            [['pd_id'], 'exist', 'skipOnError' => true, 'targetClass' => PostDeliveryKinds::className(), 'targetAttribute' => ['pd_id' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectsStates::className(), 'targetAttribute' => ['state_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectsTypes::className(), 'targetAttribute' => ['type_id' => 'id']],
            ['tpPad', 'safe'],
            // собственные правила валидации
            ['track_num', 'validateTrackNumber', 'skipOnEmpty' => false],
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
            'ready_at' => 'Дата и время подготовки',
            'sent_at' => 'Дата и время отправки',
            'delivered_at' => 'Дата и время доставки',
            'fo_project_id' => 'ID проекта',
            'fo_id_company' => 'Контрагент',
            'customer_name' => 'Контрагент',
            'state_id' => 'Статус',
            'type_id' => 'Тип',
            'pad' => 'Виды документов',
            'pd_id' => 'Способ доставки',
            'track_num' => 'Трек-номер',
            'other' => 'Другие документы',
            'comment' => 'Примечание',
            // вычисляемые поля
            'stateName' => 'Статус',
            'typeName' => 'Тип',
            'pdName' => 'Способ доставки',
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
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            //... тут ваш код

            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        if (isset($changedAttributes['state_id'])) {
            // проверим, отличается ли текущий статус от нового
            if ($changedAttributes['state_id'] != $this->state_id)
                // статус отличается, зафиксируем время назначения некоторых статусов
                switch ($this->state_id) {
                    case ProjectsStates::STATE_ОЖИДАЕТ_ОТПРАВКИ:
                        $this->ready_at = time();
                        $this->save();
                        break;
                    case ProjectsStates::STATE_ОТПРАВЛЕНО:
                        $this->sent_at = time();
                        $this->save();
                        break;
                    case ProjectsStates::STATE_ДОСТАВЛЕНО:
                        if ($this->delivered_at == null) {
                            $this->delivered_at = time();
                            $this->save();
                        }

                        break;
                }

            // если изменился статус проекта, то меняем его и в CRM
            $historyModel = foProjects::findOne(['ID_LIST_PROJECT_COMPANY' => $this->fo_project_id]);
            if ($historyModel != null) {
                $historyModel->ID_PRIZNAK_PROJECT = $this->state_id;
                $historyModel->save();
            }
        }
    }

    /**
     * Собственное правило валидации.
     */
    public function validateTrackNumber()
    {
        if ($this->pd_id == PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ || $this->pd_id == PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS) {
            if ($this->track_num == null)
                $this->addError('track_num', 'Поле обязательно для заполнения при выбранном способе.');
        }
    }

    /**
     * Берет актуальные на момент исполнения виды документов. Проставляет галочки в тех из них, которые отметил
     * пользователь (отмеченные пользователем находятся в виртуальном поле tpPad).
     * @return array
     */
    public function convertPadTableToArray()
    {
        $padKinds = PadKinds::find()->select(['id', 'name', 'name_full', 'is_provided' => new Expression(0)])->orderBy('name_full')->asArray()->all();

        if (is_array($this->tpPad) && count($this->tpPad) > 0)
            foreach ($this->tpPad as $index => $document) {
                $key = array_search($index, array_column($padKinds, 'id'));
                if (false !== $key) $padKinds[$key]['is_provided'] = true;
            }

        return json_encode($padKinds);
    }

    /**
     * Выполняет конвертацию данных из поля pad текущей модели и преобразует их в объект ArrayDataProvider.
     * @return ArrayDataProvider
     */
    public function convertPadToDataProvider()
    {
        return new ArrayDataProvider([
            //'modelClass' => 'common\models\ReportAnalytics',
            'allModels' => json_decode($this->pad),
            //'key' => 'table1_id', // поле, которое заменяет primary key
            'pagination' => false,
            'sort' => [
//                'defaultOrder' => ['table1_count' => SORT_DESC],
//                'attributes' => [
//                    'table1_id',
//                    'table1_name',
//                    'table1_count',
//                ],
            ],
        ]);
    }

    /**
     * Выполняет создание задачи для менеджера через API Fresh Office.
     * @param $type_id integer идентификатор типа задачи
     * @param $ca_id integer идентификатор контрагента, который привязывается к задаче
     * @param $receiver_id integer идентификатор менеджера (ответственного лица)
     * @param $note string текст задачи
     * @return array|integer|bool
     */
    public function foapi_createNewTaskForManager($type_id, $ca_id, $receiver_id, $note)
    {
        $params = [
            'company_id' => $ca_id,
            'user_id' => $receiver_id,
            'category_id' => FreshOfficeAPI::TASK_CATEGORY_СТАНДАРТНАЯ,
            'status_id' => FreshOfficeAPI::TASKS_STATUS_ЗАПЛАНИРОВАН,
            'type_id' => $type_id,
            'date_from' => date('Y-m-d\TH:i:s.u', time()),
            'date_till' => date('Y-m-d\TH:i:s.u', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"))),
            'note' => $note,
        ];

        $response = FreshOfficeAPI::makePostRequestToApi('tasks', $params);
        // проанализируем результат, который возвращает API Fresh Office
        $decoded_response = json_decode($response, true);
        if (isset($decoded_response['error'])) {
            $inner_message = '';
            if (isset($decoded_response['error']['innererror']))
                $inner_message = ' ' . $decoded_response['error']['innererror']['message'];
            // возникла ошибка при выполнении
            return 'При создании задачи возникла ошибка: ' . $decoded_response['error']['message']['value'] . $inner_message;
        }
        elseif (isset($decoded_response['d']))
            // фиксируем идентификатор задачи, которая была успешно создана
            return $decoded_response['d']['id'];

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(ProjectsStates::className(), ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса проекта.
     * @return string
     */
    public function getStateName()
    {
        return $this->state != null ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(ProjectsTypes::className(), ['id' => 'type_id']);
    }

    /**
     * Возвращает наименование типа проекта.
     * @return string
     */
    public function getTypeName()
    {
        return $this->type != null ? $this->type->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPd()
    {
        return $this->hasOne(PostDeliveryKinds::className(), ['id' => 'pd_id']);
    }

    /**
     * Возвращает наименование способа доставки корреспонденции.
     * @return string
     */
    public function getPdName()
    {
        return $this->pd != null ? $this->pd->name : '';
    }
}
