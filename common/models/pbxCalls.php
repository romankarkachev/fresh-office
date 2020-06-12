<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "cdr".
 *
 * @property integer $id
 * @property string $calldate
 * @property string $clid
 * @property string $src
 * @property string $dst
 * @property string $dcontext
 * @property string $channel
 * @property string $dstchannel
 * @property string $lastapp
 * @property string $lastdata
 * @property integer $duration
 * @property integer $billsec
 * @property string $disposition
 * @property integer $amaflags
 * @property string $accountcode
 * @property string $uniqueid
 * @property string $userfield
 * @property integer $is_new
 * @property integer $number_id
 * @property integer $website_id
 * @property integer $fo_ca_id
 *
 * @property string $stateName
 * @property string $stateElementClass
 * @property string $websiteName
 * @property string $regionName
 * @property string $srcEmployeeName
 * @property string $dstEmployeeName
 * @property string $recognitionFfp
 *
 * @property pbxWebsites $website
 * @property pbxNumbers $number
 * @property pbxEmployees $srcEmployee
 * @property pbxEmployees $dstEmployee
 * @property PbxCallsRecognitions $recognition
 */
class pbxCalls extends \yii\db\ActiveRecord
{
    const ПРИЗНАК_КОНТРАГЕНТ_ВООБЩЕ_НЕ_ИДЕНТИФИЦИРОВАН = -1;
    const ПРИЗНАК_КОНТРАГЕНТ_ИДЕНТИФИЦИРОВАН_НЕОДНОЗНАЧНО = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cdr';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_asterisk');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['calldate'], 'safe'],
            [['duration', 'billsec', 'amaflags', 'is_new', 'number_id', 'website_id'], 'integer'],
            [['clid', 'src', 'dst', 'dcontext', 'channel', 'dstchannel', 'lastapp', 'lastdata'], 'string', 'max' => 80],
            [['disposition'], 'string', 'max' => 45],
            [['accountcode'], 'string', 'max' => 20],
            [['uniqueid'], 'string', 'max' => 32],
            [['userfield'], 'string', 'max' => 255],
            [['website_id'], 'exist', 'skipOnError' => true, 'targetClass' => pbxWebsites::className(), 'targetAttribute' => ['website_id' => 'id']],
            [['number_id'], 'exist', 'skipOnError' => true, 'targetClass' => pbxNumbers::className(), 'targetAttribute' => ['number_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'calldate' => 'Calldate',
            'clid' => 'Clid',
            'src' => 'Src',
            'dst' => 'Dst',
            'dcontext' => 'Dcontext',
            'channel' => 'Channel',
            'dstchannel' => 'Dstchannel',
            'lastapp' => 'Lastapp',
            'lastdata' => 'Lastdata',
            'duration' => 'Duration',
            'billsec' => 'Billsec',
            'disposition' => 'Статус звонка',
            'amaflags' => 'Amaflags',
            'accountcode' => 'Accountcode',
            'uniqueid' => 'Uniqueid',
            'userfield' => 'Userfield',
            'is_new' => 'Is New',
            'number_id' => 'Number ID',
            'website_id' => 'Сайт',
            'fo_ca_id' => 'Контрагент',
            // вычисляемые поля
            'websiteName' => 'Сайт',
            'regionName' => 'Регион',
        ];
    }

    /**
     * Возвращает массив допустимых статусов звонков.
     * @return array
     */
    public static function fetchCallsStates()
    {
        return [
            [
                'id' => 'ANSWERED',
                'name' => 'Ответ',
                'elementClass' => 'success',
            ],
            [
                'id' => 'BUSY',
                'name' => 'Занято',
                'elementClass' => 'info',
            ],
            [
                'id' => 'FAILED',
                'name' => 'Ошибка',
                'elementClass' => 'danger',
            ],
            [
                'id' => 'NO ANSWER',
                'name' => 'Нет ответа',
                'elementClass' => 'warning',
            ],
        ];
    }

    /**
     * Форматирует количество секунд в текстовый вид, например: 00:03:48.
     * @param $duration integer
     * @return string
     */
    public static function formatConversationDuration($duration)
    {
        if ($duration <= 0) {
            return '-';
        }

        $seconds = $duration;
        $hours = floor($duration/3600);
        $minutes = floor($duration/60);
        $seconds %= 60;
        $hours %= 24;

        $strTime = strtotime(date('Y-m-d') . ' ' . $hours . ':' . $minutes . ':' . $seconds);
        if ($hours > 0)
            return date('H:i:s', $strTime);
        else
            return date('i:s', $strTime);
    }

    /**
     * Делает выборку статусов звонков и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfCallsStatesForSelect2()
    {
        return ArrayHelper::map(self::fetchCallsStates(), 'id', 'name');
    }

    /**
     * Возвращает наименование статуса звонка.
     * @return string
     */
    public function getStateName()
    {
        if (!empty($this->disposition)) {
            $sourceTable = self::fetchCallsStates();
            $key = array_search($this->disposition, array_column($sourceTable, 'id'));
            if (false !== $key) return $sourceTable[$key]['name'];
        }

        return '';
    }

    /**
     * Возвращает класс элемента статуса звонка.
     * @return string
     */
    public function getStateElementClass()
    {
        if (!empty($this->disposition)) {
            $sourceTable = self::fetchCallsStates();
            $key = array_search($this->disposition, array_column($sourceTable, 'id'));
            if (false !== $key) return ' ' . $sourceTable[$key]['elementClass'];
        }

        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWebsite()
    {
        return $this->hasOne(pbxWebsites::className(), ['id' => 'website_id']);
    }

    /**
     * Возвращает наименование сайта, с которого пришел звонок.
     * @return string
     */
    public function getWebsiteName()
    {
        return !empty($this->website) ? $this->website->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumber()
    {
        return $this->hasOne(pbxNumbers::className(), ['id' => 'number_id']);
    }

    /**
     * Возвращает название региона.
     * @return string
     */
    public function getRegionName()
    {
        return !empty($this->number) ? $this->number->region : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSrcInternalPhoneNumber()
    {
        return $this->hasOne(pbxInternalPhoneNumber::className(), ['phone_number' => 'src']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDstInternalPhoneNumber()
    {
        return $this->hasOne(pbxInternalPhoneNumber::className(), ['phone_number' => 'dst']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSrcEmployee()
    {
        return $this->hasOne(pbxEmployees::className(), ['id' => 'employee_id'])->via('srcInternalPhoneNumber');
    }

    /**
     * Возвращает имя сотрудника из поля Источник, если это внутренний звонок.
     * @return string
     */
    public function getSrcEmployeeName()
    {
        return !empty($this->srcEmployee) ? $this->srcEmployee->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDstEmployee()
    {
        return $this->hasOne(pbxEmployees::className(), ['id' => 'employee_id'])->via('dstInternalPhoneNumber');
    }

    /**
     * Возвращает имя сотрудника из поля Абонент, если это внутренний звонок.
     * @return string
     */
    public function getDstEmployeeName()
    {
        return !empty($this->dstEmployee) ? $this->dstEmployee->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecognition()
    {
        return $this->hasOne(PbxCallsRecognitions::class, ['call_id' => 'id']);
    }

    /**
     * Возвращает полный путь к файлу с распознанным текстом.
     * @return string
     */
    public function getRecognitionFfp()
    {
        return !empty($this->recognition) ? $this->recognition->ffp : '';
    }
}
