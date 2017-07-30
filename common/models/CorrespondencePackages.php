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
 * @property integer $fo_project_id
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
            [['created_at', 'ready_at', 'sent_at', 'fo_project_id', 'state_id', 'type_id', 'pd_id'], 'integer'],
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
            'fo_project_id' => 'ID проекта',
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
     * Собственное правило валидации.
     */
    public function validateTrackNumber()
    {
        if ($this->pd_id == PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ || $this->pd_id == PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS) {
            if ($this->track_num == null)
                $this->addError('track_num', 'Поле обязательно для заполнения при выбранном способе.');
            else
                // всегда и принудительно ставим статус в Отправлено
                $this->state_id = ProjectsStates::STATE_ОТПРАВЛЕНО;
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
