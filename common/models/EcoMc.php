<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * Таблица договоров сопровождения по экологическим отчетам.
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $org_id Организация-исполнитель
 * @property int $fo_ca_id Контрагент из Fresh Office
 * @property int $manager_id Ответственный по договору
 * @property string $amount Сумма
 * @property string $date_start Дата начала действия договора
 * @property string $date_finish Дата завершения действия договора
 * @property string $comment Комментарий
 *
 * @property string $createdByProfileName
 * @property string $organizationName
 * @property string $organizationShortName
 * @property string $managerProfileName
 *
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property AuthAssignment $createdByRoles
 * @property Organizations $organization
 * @property User $manager
 * @property Profile $managerProfile
 * @property AuthAssignment $managerRoles
 * @property EcoMcTp[] $ecoMcTps
 */
class EcoMc extends \yii\db\ActiveRecord
{
    /**
     * Псевдонимы присоединяемых таблиц
     */
    const JOIN_CREATOR_PROFILE_ALIAS = 'createdByProfile';
    const JOIN_CREATOR_ROLES_ALIAS = 'createdByRoles';
    const JOIN_MANAGER_PROFILE_ALIAS = 'managerProfile';
    const JOIN_MANAGER_ROLES_ALIAS = 'managerRoles';

    /**
     * @var array массив отчетов, добавленных при создании договора обслуживания
     */
    public $crudeReports;

    /**
     * @var string отчеты, включенные в договор сопровождения для вывода в списке (виртуальное поле)
     */
    public $reports;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eco_mc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fo_ca_id', 'manager_id'], 'required'],
            [['created_at', 'created_by', 'org_id', 'fo_ca_id', 'manager_id'], 'integer'],
            [['amount'], 'number'],
            [['date_start', 'date_finish'], 'safe'],
            [['comment'], 'string'],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['manager_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['org_id' => 'id']],
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
            'org_id' => 'Исполнитель',
            'fo_ca_id' => 'Контрагент из Fresh Office',
            'manager_id' => 'Ответственный по договору',
            'amount' => 'Сумма',
            'date_start' => 'Дата начала действия договора',
            'date_finish' => 'Дата завершения действия договора',
            'comment' => 'Комментарий',
            // виртуальные поля
            'crudeReports' => 'Отчеты',
            // вычисляемые поля
            'createdByProfileName' => 'Автор',
            'orgName' => 'Исполнитель',
            'managerProfileName' => 'Ответственный',
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
                'preserveNonEmptyValues' => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind() {
        parent::afterFind();
        // дробное число суммы берем как целое (иначе виджет maskedInput копейки помещает в целое, а после запятой ничего не остается)
        $this->amount = (int) $this->amount;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением объекта

            // соответствия старых и новых баз данных
            EcoMcTp::deleteAll(['mc_id' => $this->id]);

            return true;
        }

        return false;
    }

    /**
     * Рендерит необходимые кнопки для управления формой.
     * @return mixed
     */
    public function renderSubmitButtons()
    {
        $siaButtons = [
            'create' => Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']),
            'save' => Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']),
        ];

        if ($this->isNewRecord) {
            return $siaButtons['create'];
        }
        else {
            $result = $siaButtons['save'];
        }

        return $result;
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
        return $this->hasOne(Profile::class, ['user_id' => 'created_by'])->from([self::JOIN_CREATOR_PROFILE_ALIAS => Profile::tableName()]);
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
    public function getCreatedByRoles()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'created_by'])->from([self::JOIN_CREATOR_ROLES_ALIAS => AuthAssignment::tableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'org_id']);
    }

    /**
     * Возвращает наименование (внутреннее) организации-исполнителя.
     * @return string
     */
    public function getOrganizationName()
    {
        return !empty($this->organization) ? $this->organization->name : '';
    }

    /**
     * Возвращает наименование (сокращенное) организации-исполнителя.
     * @return string
     */
    public function getOrganizationShortName()
    {
        return !empty($this->organization) ? $this->organization->name_short : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagerProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'manager_id'])->from([self::JOIN_MANAGER_PROFILE_ALIAS => Profile::tableName()]);
    }

    /**
     * Возвращает имя ответственного по договору.
     * @return string
     */
    public function getManagerProfileName()
    {
        return !empty($this->managerProfile) ? (!empty($this->managerProfile->name) ? $this->managerProfile->name : $this->manager->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagerRoles()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'manager_id'])->from([self::JOIN_MANAGER_ROLES_ALIAS => AuthAssignment::tableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoMcTps()
    {
        return $this->hasMany(EcoMcTp::class, ['mc_id' => 'id']);
    }
}
