<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * This is the model class for table "eco_projects".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $responsible_id Ответственный
 * @property int $type_id Тип проекта
 * @property int $ca_id Заказчик
 * @property string $contract_amount Сумма
 * @property string $date_start Дата запуска проекта в работу
 * @property string $date_finish_contract Дата завершения проекта по договору
 * @property string $date_close_plan Планируемая дата завершения проекта
 * @property int $closed_at Фактическая дата завершения проекта
 * @property string $comment Примечание
 *
 * @property string $createdByProfileName
 * @property string $responsibleProfileName
 * @property string $typeName
 * @property string $customerName
 * @property integer $ecoProjectsMilestonesPendingCount
 * @property bool $hasCurrentProjectAccess
 *
 * @property EcoTypes $type
 * @property foCompany $customer
 * @property User $createdBy
 * @property User $responsible
 * @property Profile $createdByProfile
 * @property Profile $responsibleProfile
 * @property EcoProjectsMilestones $lastMilestone
 * @property EcoProjectsMilestones $currentMilestone
 * @property EcoProjectsAccess[] $ecoProjectsAccesses
 * @property EcoProjectsMilestones[] $ecoProjectsMilestones
 */
class EcoProjects extends \yii\db\ActiveRecord
{
    /**
     * @var string наименование текущего этапа проекта (виртуальное вычисляемое поле)
     */
    public $currentMilestoneName;

    /**
     * @var string планируемый срок выполнения текущего этапа проекта (виртуальное вычисляемое поле)
     */
    public $currentMilestoneDatePlan;

    /**
     * @var integer количество уже выполненных этапов
     */
    public $milestonesDoneCount;

    /**
     * @var integer общее количество этапов в проекте (виртуальное вычисляемое поле)
     */
    public $totalMilestonesCount;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eco_projects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'ca_id', 'date_start'], 'required'],
            [['created_at', 'created_by', 'responsible_id', 'type_id', 'ca_id', 'closed_at'], 'integer'],
            [['contract_amount'], 'number'],
            [['date_start', 'date_finish_contract', 'date_close_plan'], 'safe'],
            [['comment'], 'string'],
            [['responsible_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['responsible_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoTypes::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
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
            'responsible_id' => 'Ответственный',
            'type_id' => 'Тип проекта',
            'ca_id' => 'Заказчик',
            'contract_amount' => 'Сумма',
            'date_start' => 'Дата запуска проекта в работу',
            'date_finish_contract' => 'Дата завершения проекта по договору',
            'date_close_plan' => 'Планируемая дата завершения проекта',
            'closed_at' => 'Фактическая дата завершения проекта',
            'comment' => 'Примечание',
            // вычисляемые поля
            'createdByProfileName' => 'Автор создания',
            'responsibleProfileName' => 'Ответственный',
            'typeName' => 'Тип проекта',
            'customerName' => 'Заказчик',
            'currentMilestoneName' => 'Текущий этап',
            'milestonesDoneCount' => 'Выполнено этапов',
            'totalMilestonesCount' => 'Всего этапов',
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
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by', 'responsible_id'],
                ],
                'preserveNonEmptyValues' => true,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            // после создания дополним проект

            /**
             * Этапы проекта
             */
            $dateMilestoneClose = strtotime($this->date_start . ' 00:00:00');
            $batch = [];
            foreach (EcoTypesMilestones::find()->where(['type_id' => $this->type_id])->orderBy('order_no')->all() as $milestone) {
                /* @var $milestone EcoTypesMilestones */

                $dateMilestoneClose = $dateMilestoneClose + ($milestone['time_to_complete_required'] * 24 *3600);

                $batch[] = [
                    'project_id' => $this->id,
                    'milestone_id' => $milestone->milestone_id,
                    'is_file_reqiured' => $milestone->is_file_reqiured,
                    'is_affects_to_cycle_time' => $milestone->is_affects_to_cycle_time,
                    'time_to_complete_required' => $milestone->time_to_complete_required,
                    'order_no' => $milestone->order_no,
                    'date_close_plan' => Yii::$app->formatter->asDate($dateMilestoneClose, 'php:Y-m-d'),
                ];
            }

            // вставляем одним махом все этапы
            Yii::$app->db->createCommand()->batchInsert(EcoProjectsMilestones::tableName(), [
                'project_id',
                'milestone_id',
                'is_file_reqiured',
                'is_affects_to_cycle_time',
                'time_to_complete_required',
                'order_no',
                'date_close_plan',
            ], $batch)->execute();

            /**
             * Доступ к проекту
             * По-умолчанию, доступ имеет ответственный по проекту.
             */
            (new EcoProjectsAccess([
                'project_id' => $this->id,
                'user_id' => $this->responsible_id,
            ]))->save();
        }
    }

    /**
     * Удаление связанных объектов перед удалением текущего.
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // удаляем возможные файлы

            $transaction = Yii::$app->db->beginTransaction();
            try {
                // deleteAll не вызывает beforeDelete, поэтому делаем перебор
                $nestedRecords = EcoProjectsMilestones::find()->where(['project_id' => $this->id])->all();
                foreach ($nestedRecords as $record) $record->delete();

                // удаляем доступ пользователей к проекту
                EcoProjectsAccess::deleteAll(['project_id' => $this->id]);

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            return true;
        }

        return false;
    }

    /**
     * Формирует значение для вывода в колонке "Требуется времени" таблицы с этапами проекта.
     * @param $prevMilestoneClosedAt integer дата начала отсчета в формате php:U
     * @param $model
     * @param $key
     * @param $index
     * @param $column
     * @return string
     */
    public static function milestonesListTimeRequired($prevMilestoneClosedAt, $model, $key=null, $index=null, $column=null)
    {
        /* @var $model \common\models\EcoProjectsMilestones */
        /* @var $column \yii\grid\DataColumn */

        if (empty($column)) $column = new \yii\grid\DataColumn(['attribute' => 'time_to_complete_required']);

        $addon = ''; // количество дней, потраченных по факту и только если отличается от плана
        // если этап еще не закрыт, помещаем значение этой колонки в контейнер, чтобы можно было интерактивно изменить
        $prepend = '<div id="blockRequired' . $model->id . '">';
        $append = '</div>';
        if (!empty($model->closed_at)) {
            // по закрытым проектам не помещаем в контейнер
            $prepend = '';
            $append = '';
            // подсчет времени идет с начала дня и до конца дня
            $value = foProjects::downcounter($prevMilestoneClosedAt, strtotime(Yii::$app->formatter->asDate($model->closed_at, 'php:Y-m-d 23:59:59')), true);
            $days = \common\models\Drivers::leaveOnlyDigits($value);
            if ($model->{$column->attribute} != $days && $days >= 0) {
                $addon = ' <small class="text-muted"><em></em>по факту ' . (!empty($value) ? $value : 'в тот же день') . '</small>';
            }
        }

        return $prepend . foProjects::declension($model->{$column->attribute}, ['день','дня','дней']) . $addon . $append;
    }

    /**
     * Формирует значение для вывода в колонке "Срок" таблицы с этапами проекта.
     * @param $model
     * @param $canChangeDate bool
     * @param null $key
     * @param null $index
     * @param null $column
     * @return string
     */
    public static function milestonesListTerminColumn($model, $canChangeDate = true, $key=null, $index=null, $column=null)
    {
        /* @var $model \common\models\EcoProjectsMilestones */
        /* @var $column \yii\grid\DataColumn */

        $thumbsUp = '';
        $closedAt = Yii::$app->formatter->asDate($model->closed_at, 'php:Y-m-d');
        if (!empty($model->closed_at)) {
            // по закрытым проектам не помещаем в контейнер
            $prepend = '';
            $append = '';

            if ($model->date_close_plan == $closedAt)
                // не уверен, что есть смысл еще одну галочку выводить, если совпали сроки:
                //$isMatches = ' <i class="fa fa-check text-success" aria-hidden="true" title="Планируемый срок совпал с фактическим"></i>';
                $isMatches = '';
            else {
                if ($model->date_close_plan > $closedAt) {
                    // проект завершен раньше, чем планировалось
                    $thumbsUp = ' <i class="fa fa-thumbs-up text-success" aria-hidden="true" title="Красавчики! Этап закрыт досрочно."></i>';
                }

                $isMatches = ' (план ' . Yii::$app->formatter->asDate(strtotime($model->date_close_plan . ' 00:00:00'), 'php:d.m.Y') . ')' . $thumbsUp;
            }

            $result = Yii::$app->formatter->asDate($model->closed_at, 'php:d F Y г. в H:i') . $isMatches;
        }
        else {
            // если этап еще не закрыт, помещаем значение этой колонки в контейнер, чтобы можно было интерактивно изменить
            $prepend = '<div id="blockTermin' . $model->id . '">';
            $append = '</div>';
            $value = Yii::$app->formatter->asDate(strtotime($model->date_close_plan . ' 00:00:00'), 'php:d F Y г.');
            if ($canChangeDate === true) {
                $result = Html::a($value, '#', [
                    'class' => 'link-ajax',
                    'id' => 'changeMilestoneCloseDate' . $model->id,
                    'data-id' => $model->id,
                    'title' => 'Щелкните, чтобы изменить планируемую дату завершения проекта',
                ]);
            }
            else {
                $result = $value;
            }
        }

        return $prepend . $result . $append;
    }

    /**
     * Формирует значение для вывода в графе "Файлы" таблицы с этапами проекта.
     * @param $currentMilestone
     * @param $model
     * @param $filesCount integer
     * @param null $key
     * @param null $index
     * @param null $column
     * @return string
     */
    public static function milestonesListFilesColumn($currentMilestone, $model, $filesCount=null, $key=null, $index=null, $column=null)
    {
        if (empty($filesCount)) $filesCount = $model->filesCount;
        $caption = '<i class="fa fa-floppy-o text-primary" aria-hidden="true"' .
            (!empty($filesCount) ? ' title="Имеется ' . foProjects::declension($filesCount, ['файл', 'файла', 'файлов']) . '"' : '') .
            '></i>' .
            (!empty($filesCount) ? ' <small class="text-muted"><em>' . $filesCount . '</em></small>' : '');

        $button = Html::a($caption,
            ['/' . \backend\controllers\EcoProjectsController::ROOT_URL_FOR_SORT_PAGING . '/close-milestone', 'id' => $model->id], [
                'class' => 'btn btn-default btn-xs',
                'title' => 'Открыть форму завершения этапа',
            ]);
        if ($currentMilestone == $model->id || (empty($currentMilestone) && empty($model->closed_at))) {
            // если это текущий этап, то в него можно добавить файлы
            if ($model->is_file_reqiured)
                $tool = $button;
            else
                return '';
        }
        else {
            // в другие этапы - завершенные или еще не открытые - файлы добавить нельзя
            if (empty($model->closed_at)) {
                if ($model->is_file_reqiured)
                    $tool = '<i class="fa fa-floppy-o text-muted" aria-hidden="true" title="Для закрытия этапа обязательно необходимо преодставить минимум один файл"></i>';
                else
                    return '';
            }
            else
                if ($model->is_file_reqiured)
                    $tool = $button;
                else
                    return '';
        }

        return '<div id="blockFiles' . $model->id . '">' . $tool . '</div>';
    }

    /**
     * Формирует значение для вывода в колонке "Состояние" таблицы с этапами проекта.
     * @param $currentMilestone integer
     * @param $model
     * @param null $key
     * @param null $index
     * @param null $column
     * @return string
     */
    public static function milestonesListToolColumn($currentMilestone, $model, $key=null, $index=null, $column=null)
    {
        /* @var $model \common\models\EcoProjectsMilestones */
        /* @var $column \yii\grid\DataColumn */

        /*
        // только для отладки:
        return '<div id="blockTool' . $model->id . '">' . Html::button('<i class="fa fa-check-circle" aria-hidden="true"></i> Завершить', [
                'class' => 'btn btn-success btn-xs',
                'id' => 'closeMilestone' . $model->id,
                'data-id' => $model->id,
                'title' => 'Закрыть этап немедленно',
            ]) . '</div>';
        */
        $tool = '';

        if (!empty($model->closed_at))
            return '<i class="fa fa-check-circle text-success" aria-hidden="true" title="Этап закрыт"></i>';
        elseif (empty($model->closed_at) && (empty($currentMilestone))) {
            // этапы, для которых предоставление файла не требуется, позволяем закрыть мгновенно - при помощи ajax
            // а этапы, требущие файлов, закрываются через отдельную форму
            // если для закрытия этапа требуются файлы, но некоторые из них уже были предоставлены ранее, то
            // позволяем закрыть этап мгновенно
            if ($model->is_file_reqiured && empty($model->filesCount)) {
                $tool = Html::a('Завершить...', ['/' . \backend\controllers\EcoProjectsController::ROOT_URL_FOR_SORT_PAGING . '/close-milestone', 'id' => $model->id], [
                    'class' => 'btn btn-default btn-xs',
                    'title' => 'Открыть форму завершения этапа',
                ]);
            }
            else {
                $tool = Html::button('<i class="fa fa-flag-checkered" aria-hidden="true"></i> Завершить', [
                    'class' => 'btn btn-success btn-xs',
                    'id' => 'closeMilestone' . $model->id,
                    'data-id' => $model->id,
                    'title' => 'Закрыть этап немедленно',
                ]);
            }
        }

        return '<div id="blockTool' . $model->id . '">' . $tool . '</div>';
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
     * Возвращает имя создателя проекта по экологии.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return !empty($this->createdByProfile) ? $this->createdByProfile->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResponsible()
    {
        return $this->hasOne(User::className(), ['id' => 'responsible_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResponsibleProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'responsible_id'])->from(['responsibleProfile' => 'profile']);
    }

    /**
     * Возвращает имя ответственного.
     * @return string
     */
    public function getResponsibleProfileName()
    {
        return !empty($this->responsibleProfile)? $this->responsibleProfile->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(EcoTypes::className(), ['id' => 'type_id']);
    }

    /**
     * Возвращает наименование типа проекта.
     * @return string
     */
    public function getTypeName()
    {
        return !empty($this->type) ? $this->type->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(foCompany::className(), ['ID_COMPANY' => 'ca_id']);
    }

    /**
     * Возвращает наименование типа проекта.
     * @return string
     */
    public function getCustomerName()
    {
        return !empty($this->customer) ? $this->customer->COMPANY_NAME : '';
    }

    /**
     * Определяет последний завершенный этап.
     * @return \yii\db\ActiveQuery
     */
    public function getLastMilestone()
    {
        return $this->hasOne(EcoProjectsMilestones::className(), ['project_id' => 'id'])->where('`closed_at` IS NOT NULL')->orderBy('closed_at DESC');
    }

    /**
     * Определяет текущий этап.
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentMilestone()
    {
        return $this->hasOne(EcoProjectsMilestones::className(), ['project_id' => 'id'])->where('`closed_at` IS NULL')->orderBy('order_no');
    }

    /**
     * Определяет, имеет ли текущий пользователь доступ к данному проекту.
     * @return bool
     */
    public function getHasCurrentProjectAccess()
    {
        return $this->hasOne(EcoProjectsAccess::className(), ['project_id' => 'id'])->where(['user_id' => Yii::$app->user->id])->count() > 0;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoProjectsAccesses()
    {
        return $this->hasMany(EcoProjectsAccess::className(), ['project_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoProjectsMilestones()
    {
        return $this->hasMany(EcoProjectsMilestones::className(), ['project_id' => 'id']);
    }

    /**
     * Возвращает количество незакрытых этапов проекта.
     * @return integer
     */
    public function getEcoProjectsMilestonesPendingCount()
    {
        return $this->hasMany(EcoProjectsMilestones::className(), ['project_id' => 'id'])->where('closed_at IS NULL')->count();
    }
}
