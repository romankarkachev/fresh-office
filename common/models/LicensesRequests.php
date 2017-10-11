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
 * @property integer $ca_email
 * @property string $ca_name
 * @property integer $ca_id
 * @property string $comment
 * @property string $fkkosTextarea
 * @property array $tpFkkos
 *
 * @property string $createdByName
 * @property string $stateName
 *
 * @property LicensesRequestsStates $state
 * @property User $createdBy
 * @property Profile $createdByProfile
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
            [['state_id', 'ca_email'], 'required'],
            [['created_at', 'created_by', 'state_id', 'ca_email', 'ca_id'], 'integer'],
            [['comment'], 'string'],
            [['ca_name'], 'string', 'max' => 255],
            [['fkkosTextarea', 'tpFkkos'], 'safe'],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => LicensesRequestsStates::className(), 'targetAttribute' => ['state_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            // собственные правила валидации
            ['fkkosTextarea', 'validateFkkos', 'skipOnEmpty' => false],
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
            'comment' => 'Примечание',
            'fkkosTextarea' => 'Коды ФККО',
            // вычисляемые поля
            'createdByName' => 'Менеджер',
            'stateName' => 'Статус запроса',
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

                if ($model != null) {
                    $fkkoPages = new LicensesFkkoPages([
                        'fkko_id' => $model->id,
                    ]);
                    $this->tpFkkos[] = $fkkoPages;
                }
                else
                    $this->addError('tpFkkos', 'Код ФККО ' . $fkko . ' не обнаружен!');
            }
        }
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
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
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
}
