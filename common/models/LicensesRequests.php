<?php

namespace common\models;

use Yii;
use dektrium\user\models\Profile;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "licenses_requests".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $state_id
 * @property string $ca_email
 * @property string $ca_name
 * @property integer $ca_id
 * @property integer $org_id
 * @property string $receivers_email
 * @property string $comment
 * @property string $fkkosTextarea
 * @property array $tpFkkos
 *
 * @property string $createdByName
 * @property string $createdByEmail
 * @property string $stateName
 * @property string $organizationName
 *
 * @property LicensesRequestsStates $state
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property Organizations $organization
 * @property LicensesRequestsFkko[] $licensesRequestsFkkos
 */
class LicensesRequests extends \yii\db\ActiveRecord
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
     * Список заказанных менеджером кодов отходов.
     * Виртуальное вычисляемое поле.
     * @var string
     */
    public $fkkos;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'licenses_requests';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['state_id', 'ca_email', 'org_id', 'receivers_email'], 'required'],
            [['created_at', 'created_by', 'state_id', 'ca_id', 'org_id'], 'integer'],
            [['comment'], 'string'],
            [['comment'], 'default', 'value' => null],
            [['ca_email', 'ca_name', 'receivers_email'], 'string', 'max' => 255],
            ['ca_email', 'trim'],
            ['ca_email', 'email'],
            [['fkkosTextarea', 'tpFkkos', 'fkkos'], 'safe'],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::className(), 'targetAttribute' => ['org_id' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => LicensesRequestsStates::className(), 'targetAttribute' => ['state_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            // собственные правила валидации
            ['fkkosTextarea', 'validateFkkos', 'skipOnEmpty' => false],
            ['ca_email', 'validateEmail'],
            ['state_id', 'validateState'],
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
            'state_id' => 'Статус запроса',
            'ca_email' => 'E-mail контрагента',
            'ca_name' => 'Контрагент',
            'ca_id' => 'Контрагент',
            'org_id' => 'Организация',
            'receivers_email' => 'E-mail получателя сканов лицензий в случае одобрения',
            'comment' => 'Примечание',
            'fkkosTextarea' => 'Коды ФККО',
            'fkkos' => 'Коды ФККО',
            // вычисляемые поля
            'createdByName' => 'Менеджер',
            'stateName' => 'Статус запроса',
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
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
            ],
        ];
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
                $page = LicensesFkkoPages::find()->joinWith('file')->where(['fkko_id' => $model->id, 'licenses_files.organization_id' => $this->org_id])->one();

                // код ФККО может быть использован, только если он существует в нашей базе, а также если есть
                // отсканированная страница с этим кодом
                if ($model != null && !empty($page)) {
                    $lrFkko = new LicensesRequestsFkko([
                        'fkko_id' => $model->id,
                        'file_id' => $page->file_id,
                    ]);

                    $this->tpFkkos[] = $lrFkko;
                }
                else
                    $this->addError('tpFkkos', 'Код ФККО ' . $fkko . ' не обнаружен!');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function validateEmail()
    {
        $ca = DirectMSSQLQueries::tryToIdentifyCounteragent($this->ca_email);
        if (false != $ca) {
            $this->ca_id = $ca['caId'];
            $this->ca_name = $ca['caName'];
        }
        else
            $this->addError('ca_email', 'Контрагент не идентифицирован.');
    }

    /**
     * @inheritdoc
     */
    public function validateState()
    {
        if ($this->state_id == LicensesRequestsStates::LICENSE_STATE_ОТКАЗ && $this->comment == null)
            $this->addError('comment', 'При отказе заполнение комментария обязательно.');
    }

    /**
     * Перед удалением информации о прикрепленном к сделке файле, удалим его физически с диска.
     * @return bool
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            LicensesRequestsFkko::deleteAll(['lr_id' => $this->id]);

            return true;
        }
        else return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * Возвращает Email пользователя, который создал запрос.
     * @return string
     */
    public function getCreatedByEmail()
    {
        return $this->created_by != null ? $this->createdBy->email : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by']);
    }

    /**
     * Возвращает имя автора.
     * @return string
     */
    public function getCreatedByName()
    {
        return $this->created_by == null ? '' : ($this->createdByProfile == null ? $this->createdBy->username : $this->createdByProfile->name);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(LicensesRequestsStates::className(), ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса запроса.
     * @return string
     */
    public function getStateName()
    {
        return $this->state != null ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::className(), ['id' => 'org_id']);
    }

    /**
     * Возвращает наименование организации, сканы лицензии которой запрашиваются.
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organization != null ? $this->organization->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicensesRequestsFkkos()
    {
        return $this->hasMany(LicensesRequestsFkko::className(), ['lr_id' => 'id']);
    }
}
