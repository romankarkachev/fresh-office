<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "appeals".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $state_id
 * @property string $form_company
 * @property string $form_username
 * @property string $form_region
 * @property string $form_phone
 * @property string $form_email
 * @property string $form_message
 * @property integer $fo_id_company
 * @property string $fo_company_name
 * @property integer $ca_state_id
 * @property integer $as_id
 * @property string $request_referrer
 * @property string $request_user_agent
 * @property string $request_user_ip
 *
 * @property string $appealStateName
 * @property string $caStateName
 * @property string $asName
 *
 * @property AppealSources $as
 */
class Appeals extends \yii\db\ActiveRecord
{
    /**
     * Статусы клиентов.
     */
    const CA_STATE_NEW = 0; // Новый (контрагент вообще отсутствует)
    const CA_STATE_ACTUAL = 1; // Действующий (есть записи в разделе Финансы, находится в ЦОД или ВИП)
    const CA_STATE_AMBIGUOUS = 2; // Неоднозначный (в результате выборки несколько подходящих записей)
    const CA_STATE_REPEATED = 3; // Повторно (с этим клиентом уже работали)
    const CA_STATE_DUPLICATE = 4; // Дубль (клиент, находящийся в разработке, обращается с другого ресурса)

    /**
     * Статусы обращений.
     */
    const APPEAL_STATE_NEW = 1; // Новое
    const APPEAL_STATE_RESPONSIBLE = 2; // Выбор ответственного
    const APPEAL_STATE_PAYMENT = 3; // Ожидает оплаты
    const APPEAL_STATE_CLOSED = 4; // Закрыто
    const APPEAL_STATE_SUCCESS = 5; // Конверсия
    const APPEAL_STATE_REJECT = 6; // Отказ

    /**
     * Шаблон текста сообщения при передаче контрагента другому менеджеру.
     */
    const TEMPLATE_MESSAGE_BODY_DELEGATING_COUNTERAGENT = 'Вам передана компания: %COMPANY_NAME%.';

    /**
     * Ответственный по контрагенту.
     * @var integer
     */
    public $fo_id_manager;

    /**
     * Прикрепленные к обращению файлы.
     * @var array
     */
    public $files;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'appeals';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'state_id', 'fo_id_company', 'ca_state_id', 'as_id'], 'integer'],
            [['form_message'], 'string'],
            [['form_company'], 'string', 'max' => 50],
            [['form_username', 'form_region', 'form_phone', 'form_email', 'fo_company_name'], 'string', 'max' => 150],
            [['request_referrer', 'request_user_agent'], 'string', 'max' => 255],
            [['request_user_ip'], 'string', 'max' => 30],
            // для ввода вручную немного другие правила валидации
            ['as_id', 'required', 'on' => 'create_manual'],
            ['form_phone', 'validatePhone', 'on' => 'create_manual'],
            ['form_email', 'email', 'on' => 'create_manual'],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 10],
            [['as_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppealSources::className(), 'targetAttribute' => ['as_id' => 'id']],
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
            'state_id' => 'Статус обращения',
            // поля формы (6):
            'form_company' => 'Компания',
            'form_username' => 'Имя',
            'form_region' => 'Регион',
            'form_phone' => 'Телефон',
            'form_email' => 'Email',
            'form_message' => 'Текст сообщения',
            'fo_id_company' => 'Контрагент из Fresh Office',
            'fo_company_name' => 'Контрагент', // Наименование контрагента из Fresh Office
            'fo_id_manager' => 'Ответственный', // поле виртуальное, в базе не хранится
            'ca_state_id' => 'Статус контрагента',
            'as_id' => 'Источник обращения',
            'request_referrer' => 'Поле post-запроса Referer',
            'request_user_agent' => 'Поле post-запроса userAgent',
            'request_user_ip' => 'IP отправителя',
            'files' => 'Файлы',
            // для сортировки
            'appealSourceName' => 'Источник обращения',
            'appealStateName' => 'Статус обращения',
            'caStateName' => 'Статус клиента',
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
     * Собственное правило валидации для номера телефона.
     */
    public function validatePhone()
    {
        $phone_processed = preg_replace("/[^0-9]/", '', $this->form_phone);
        if (strlen($phone_processed) < 10)
            $this->addError('form_phone', 'Номер телефона должен состоять из 10 цифр.');
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->scenario == 'create_manual') {
                // убираем из номера телефона все символы кроме цифр и предваряем его кодом страны
                $this->form_phone = preg_replace("/[^0-9]/", '', $this->form_phone);
                // пытаемся идентифицировать контрагента
                $this->fillStates($this->tryToIdentifyCounteragent());
            }

            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        // отправляем уведомление ответственному
        // если контрагент идентифицирован, есть ответственный, у него не пустой email
        $this->sendEmailIfFilesAre();
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением
            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = AppealsFiles::find()->where(['appeal_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            return true;
        }

        return false;
    }

    /**
     * Возвращает в виде массива разновидности статусов клиентов.
     * @return array
     */
    public static function fetchCaStates()
    {
        return [
            [
                'id' => self::CA_STATE_NEW,
                'name' => 'Входящий',
            ],
            [
                'id' => self::CA_STATE_ACTUAL,
                'name' => 'Действующий',
            ],
            [
                'id' => self::CA_STATE_AMBIGUOUS,
                'name' => 'Неоднозначный',
            ],
            [
                'id' => self::CA_STATE_REPEATED,
                'name' => 'Повторно',
            ],
            [
                'id' => self::CA_STATE_DUPLICATE,
                'name' => 'Дубль',
            ],
        ];
    }

    /**
     * Возвращает в виде массива разновидности статусов обращений.
     * @return array
     */
    public static function fetchAppealStates()
    {
        return [
            [
                'id' => self::APPEAL_STATE_NEW,
                'name' => 'Новое',
            ],
            [
                'id' => self::APPEAL_STATE_RESPONSIBLE,
                'name' => 'Выбор ответственного',
            ],
            [
                'id' => self::APPEAL_STATE_PAYMENT,
                'name' => 'Ожидает оплаты',
            ],
            [
                'id' => self::APPEAL_STATE_CLOSED,
                'name' => 'Закрыто',
            ],
            [
                'id' => self::APPEAL_STATE_SUCCESS,
                'name' => 'Конверсия',
            ],
            [
                'id' => self::APPEAL_STATE_REJECT,
                'name' => 'Отказ',
            ],
        ];
    }

    /**
     * Делает выборку статусов клиентов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfCaStatesForSelect2()
    {
        return ArrayHelper::map(self::fetchCaStates() , 'id', 'name');
    }

    /**
     * Выполняет загрузку файлов из post-параметров, а также сохранение их имен в базу данных.
     * Каждый файл прикрепляется к текущему обращению.
     * @return bool
     */
    public function upload()
    {
        if ($this->validate()) {
            // путь к папке, куда будет загружен файл
            $pifp = AppealsFiles::getUploadsFilepath();
            if ($pifp !== false) {
                foreach ($this->files as $file) {
                    $fileAttached_fn = mb_strtolower(Yii::$app->security->generateRandomString() . '.' . $file->extension, 'utf-8');
                    $fileAttached_ffp = $pifp . '/' . $fileAttached_fn;

                    if ($file->saveAs($fileAttached_ffp)) {
                        // заполняем поля записи в базе о загруженном успешно файле
                        $fileAttachedmodel = new AppealsFiles();
                        $fileAttachedmodel->appeal_id = $this->id;
                        $fileAttachedmodel->ffp = $fileAttached_ffp;
                        $fileAttachedmodel->fn = $fileAttached_fn;
                        $fileAttachedmodel->ofn = $file->name;
                        $fileAttachedmodel->size = filesize($fileAttached_ffp);
                        $fileAttachedmodel->save();
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Пытается идентифицировать контрагента по имеющимся в модели контактным данным.
     * @return array
     */
    public function tryToIdentifyCounteragent()
    {
        // идентификация по наименованию (если задано)
        $company = trim($this->form_company);
        if ($company != '') {
            $query_text = '
SELECT DISTINCT COMPANY.ID_COMPANY AS caId, COMPANY_NAME AS caName,
             MANAGERS.ID_MANAGER AS managerId, MANAGERS.MANAGER_NAME AS managerName,
             ISNULL(COUNT_FINANCE, 0) AS financeCount,
             (CASE WHEN COUNT_FINANCE = 0 THEN ' . self::CA_STATE_NEW . ' ELSE ' . self::CA_STATE_ACTUAL . ' END) AS stateId
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ . ' AND ID_NAPR = ' . FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД . '
	GROUP BY ID_COMPANY
) AS FINANCES ON FINANCES.ID_COMPANY = COMPANY.ID_COMPANY
WHERE COMPANY_NAME LIKE \'%' . $company . '%\' AND COMPANY_NAME IS NOT NULL
ORDER BY COMPANY_NAME';
            // пока что так, потому что если имя задано Виктория, находит контрагента с таким наименованием, а это неверно
            //$result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
            //if (count($result) > 0) return $result;
        }

        // идентификация по номеру телефона (если задан)
        // в номере телефона убираются восьмерка в начале или семерка при достаточном количестве символов
        if (trim($this->form_phone) != '') {
            $phone_ready = $this->form_phone;
            $phone_ready = preg_replace("/[^0-9]/", '', $phone_ready);
            if (strlen($phone_ready) == 11)
                if ($phone_ready[0] == 7 || $phone_ready[0] == 8)
                    $phone_ready = substr($phone_ready, 1);
            $query_text = '
SELECT DISTINCT LIST_TELEPHONES.ID_COMPANY AS caId, COMPANY.COMPANY_NAME AS caName,
             MANAGERS.ID_MANAGER AS managerId, MANAGERS.MANAGER_NAME AS managerName,
             STUFF((SELECT \', \' + TELEPHONE FROM LIST_TELEPHONES LT WHERE LT.ID_COMPANY = LIST_TELEPHONES.ID_COMPANY FOR XML PATH(\'\')), 1, 1, \'\') AS contact,
             ISNULL(COUNT_FINANCE, 0) AS financeCount,
             (CASE WHEN ISNULL(COUNT_FINANCE, 0) = 0 THEN ' . self::CA_STATE_NEW . ' ELSE ' . self::CA_STATE_ACTUAL . ' END) AS stateId
FROM CBaseCRM_Fresh_7x.dbo.LIST_TELEPHONES
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_TELEPHONES.ID_COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ . ' AND ID_NAPR = ' . FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД . '
	GROUP BY ID_COMPANY
) AS FINANCES ON FINANCES.ID_COMPANY = LIST_TELEPHONES.ID_COMPANY
WHERE TELEPHONE LIKE \'%' . $phone_ready . '%\' AND COMPANY_NAME IS NOT NULL
ORDER BY COMPANY_NAME';
            $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
            if (count($result) > 0) return $result;
        }

        // идентификация по email (если задан)
        $email = trim($this->form_email);
        if ($email != '') {
            $query_text = '
SELECT DISTINCT COMPANY.ID_COMPANY AS caId, COMPANY.COMPANY_NAME AS caName,
             MANAGERS.ID_MANAGER AS managerId, MANAGERS.MANAGER_NAME AS managerName,
             STUFF((SELECT \', \' + email FROM LIST_EMAIL_CLIENT LEC WHERE LEC.ID_COMPANY = LIST_EMAIL_CLIENT.ID_COMPANY FOR XML PATH(\'\')), 1, 1, \'\') AS contact,
             ISNULL(COUNT_FINANCE, 0) AS financeCount,
             (CASE WHEN ISNULL(COUNT_FINANCE, 0) = 0 THEN ' . self::CA_STATE_NEW . ' ELSE ' . self::CA_STATE_ACTUAL . ' END) AS stateId
FROM CBaseCRM_Fresh_7x.dbo.LIST_EMAIL_CLIENT
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_EMAIL_CLIENT.ID_COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ . ' AND ID_NAPR = ' . FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД . '
	GROUP BY ID_COMPANY
) AS FINANCES ON FINANCES.ID_COMPANY = LIST_EMAIL_CLIENT.ID_COMPANY
WHERE email LIKE \'%' . $email . '%\' AND COMPANY_NAME IS NOT NULL
ORDER BY COMPANY_NAME';
            $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
            if (count($result) > 0) return $result;
        }

        // ничего найти не удалось
        return [];
    }

    /**
     * Заполняет данные по успешно идентифицированному контрагенту на основании значений из переданного параметра.
     * @param $dbRow array массив значений, которые будут назначены
     */
    public function fillUpIdentifiedCounteragentsFields($dbRow)
    {
        $this->fo_id_company = $dbRow['caId'];
        $this->fo_company_name = $dbRow['caName'];
        $this->fo_id_manager = $dbRow['managerId'];
        $this->ca_state_id = $dbRow['stateId'];
    }

    /**
     * Заполняет состояние клиента и обращения в зависимости от результатов идентификации контрагента.
     * @param $matches array
     */
    public function fillStates($matches)
    {
        if (count($matches) > 0) {
            // в результате выборки вообще есть варианты
            if (count($matches) == 1) {
                // контрагент идентифицирован однозначно
                $this->fillUpIdentifiedCounteragentsFields($matches[0]);
                if ($this->ca_state_id == Appeals::CA_STATE_ACTUAL) {
                    // это действующий клиент
                    // просто ставим статусы клиента "Действующий" и обращения "Закрыто"
                    $this->state_id = Appeals::APPEAL_STATE_CLOSED;
                    // создание задачи текущему ответственному о том, что обратился клиент,
                    // с которым мы работаем
                    // в функции применяется подстановка ответственных
                    self::foapi_createNewTaskForManager($this->fo_id_company, $this->fo_id_manager, $this->form_message);
                }
                else {
                    // теперь необходимо разобраться: мы работали с клиентом уже (статус "Повторно")
                    // или он просто дублирует заявку с другого ресурса
                    // выборка ответственных по отказам
                    $responsibleRefusal = ResponsibleRefusal::find()->select('responsible_id')->asArray()->column();
                    if (count($responsibleRefusal) > 0) {
                        if (in_array($this->fo_id_manager, $responsibleRefusal)) {
                            // если ответственный идентифицированного контрагента входит в список
                            // ответственных по отказам (БАНК или БАНК ВХОДЯЩИЕ на момент написания кода)
                            // обращение принимает статусы клиента "Повторно" и обращения "Выбор ответственного"
                            $this->ca_state_id = Appeals::CA_STATE_REPEATED;
                            $this->state_id = Appeals::APPEAL_STATE_RESPONSIBLE;
                        }
                        else {
                            // ответственный не является отказником, значит, заказчик просто дублирует заявку
                            // обращение принимает статусы клиента "Дубль" и обращения "Закрыто"
                            $this->ca_state_id = Appeals::CA_STATE_DUPLICATE;
                            $this->state_id = Appeals::APPEAL_STATE_CLOSED;
                            // создание задачи текущему ответственному о том, что обратился клиент,
                            // с которым мы работаем
                            // в функции применяется подстановка ответственных
                            self::foapi_createNewTaskForManager(
                                $this->fo_id_company,
                                $this->fo_id_manager,
                                'Уважаемый менеджер! Клиент дублирует заявку со следующим обращением: ' . chr(13) . $this->form_message
                            );
                        }
                    }
                    else {
                        // если ответственные-отказники не назначены, тогда мы не знаем, что делать с этим
                        // обращением, поставим статусы "Неоднозначный" и "Новое"
                        $this->ca_state_id = Appeals::CA_STATE_AMBIGUOUS;
                        $this->state_id = Appeals::APPEAL_STATE_NEW;
                    }
                }
            }
            else {
                // контрагент не может быть идентифицирован однозначно
                // то есть в результате выборки несколько подходящих записей
                // статусы "Неоднозначный" и "Новое"
                $this->ca_state_id = Appeals::CA_STATE_AMBIGUOUS;
                $this->state_id = Appeals::APPEAL_STATE_NEW;
            }
        }
        else {
            // контрагент вообще не идентифицирован
            // статусы "Новый" и "Новое"
            $this->ca_state_id = Appeals::CA_STATE_NEW;
            $this->state_id = Appeals::APPEAL_STATE_NEW;
        }
    }

    /**
     * Отправляет приаттаченные к обращению файлы на почту ответственного менеджера.
     */
    public function sendEmailIfFilesAre()
    {
        // отправляется, только если клиент идентифицирован и есть что отправлять
        $files = AppealsFiles::find()->where(['appeal_id' => $this->id])->andWhere(['sent_at' => null])->all();
        if ($this->fo_id_company != null && count($files) > 0) {
            // определим ответственного
            $responsible_email = '';
            $responsible = $this->getCounteragentsReliable();
            if (count($responsible) > 0) {
                // берем только первый элемент массива, их там и не должно вообще быть больше
                $responsible = $responsible[0];

                if (isset($responsible['id'])) {
                    $responsible_id = intval($responsible['id']);
                    // проверим, не входит ли текущий ответственный в список отказников
                    $responsibleRefusal = ResponsibleRefusal::find()->select('responsible_id')->asArray()->column();
                    if (count($responsibleRefusal) > 0)
                        // если ответственный в списке отказников, то отправлять email не будем
                        // во всяком случае в этот раз, но когда при следующем сохранении будет обнаружен другой
                        // ответственный, то можно будет попытаться еще раз отправить
                        if (in_array($responsible_id, $responsibleRefusal)) return false;

                    // проверим, не входит ли текущий ответственный в список подмены
                    // возможно, это ЦОД или ВИП. тогда подменим на реальных людей
                    $rs = ResponsibleSubstitutes::find()->select('required_id,substitute_id')->asArray()->all();
                    if (count($rs) > 0) {
                        // выборка ответственных для подмены успешно выполнена, есть записи
                        $key = array_search($responsible_id, array_column($rs, 'required_id'));
                        // проверим, не входит ли переданный ответственный в список подменяемых и
                        // заменим на реального, если входит
                        if (false !== $key) $responsible = $this->getResponsible(intval($rs[$key]['substitute_id']));
                    };
                }

                if (isset($responsible['email']))
                    if ($responsible['email'] != null && $responsible['email'] != '')
                        $responsible_email = trim($responsible['email']);
            }
            else {
                $responsible_email = Yii::$app->params['receiverEmail'];
            }

            if ($responsible_email != null && $responsible_email != '') {
                // только если есть файлы и ответственный
                $params['appeal'] = $this;

                $letter = Yii::$app->mailer->compose([
                    'html' => 'filesAttachedToAppeal-html',
                ], $params)
                    ->setFrom(Yii::$app->params['senderEmail'])
                    ->setTo($responsible_email)
                    ->setSubject('Создано новое обращение');

                foreach ($files as $file)
                    /* @var $file AppealsFiles */
                    $letter->attach($file->ffp);

                if ($letter->send()) {
                    AppealsFiles::updateAll([
                        'sent_at' => time(),
                    ], [
                        'appeal_id' => $this->id,
                    ]);
                }
            };
        }
    }

    /**
     * Выполняет создание сообщения для менеджера через API Fresh Office.
     * @param $sender_id integer идентификатор менеджера-отправителя сообщения
     * @param $receiver_id integer идентификатор менеджера-получателя сообщения
     * @param $message string текст сообщения, которое будет отправлено менеджеру
     * @return array|integer|bool
     */
    public static function foapi_createNewMessageForManager($sender_id, $receiver_id, $message)
    {
        $params = [
            'user_id' => $receiver_id,
            //'user_id' => 38, // temporary1, для тестов
            'sender_id' => $sender_id,
            'text' => $message,
            // если не заполнять, то заполняется автоматически
            //'created' => '2017-04-07T16:25:00', // date('Y-m-d\TH:i:s.u', time())
            'type_id' => FreshOfficeAPI::MESSAGES_TYPE_СООБЩЕНИЕ,
            'status_id' => FreshOfficeAPI::MESSAGES_STATUS_НЕПРОЧИТАНО,
        ];

        // дата не проставляется автоматически - глюк API Fresh office
        $response = FreshOfficeAPI::makePostRequestToApi('messages', $params);
        // проанализируем результат, который возвращает API Fresh Office
        $decoded_response = json_decode($response, true);
        if (isset($decoded_response['error'])) {
            $inner_message = '';
            if (isset($decoded_response['error']['innererror']))
                $inner_message = ' ' . $decoded_response['error']['innererror']['message'];
            // возникла ошибка при выполнении
            return 'При создании сообщения возникла ошибка: ' . $decoded_response['error']['message']['value'] . $inner_message;
        }
        elseif (isset($decoded_response['d']))
            // фиксируем идентификатор сообщения, которое было успешно создано
            return $decoded_response['d']['id'];

        return false;
    }

    /**
     * Выполняет создание задачи для менеджера через API Fresh Office.
     * @param $ca_id integer идентификатор контрагента, который привязывается к задаче
     * @param $receiver_id integer идентификатор менеджера-исполнителя задачи
     * @param $note string текст задачи
     * @return array|integer|bool
     */
    public static function foapi_createNewTaskForManager($ca_id, $receiver_id, $note)
    {
        // для начала необходимо выполнить проверку: не требуется ли замена ответственного на реального человека
        $rs = ResponsibleSubstitutes::find()->select('required_id,substitute_id')->asArray()->all();
        if (count($rs) > 0) {
            // выборка ответственных для подмены успешно выполнена, есть записи
            $key = array_search($receiver_id, array_column($rs, 'required_id'));
            // проверим, не входит ли переданный ответственный в список подменяемых и заменим на реального, если входит
            if (false !== $key) $receiver_id = intval($rs[$key]['substitute_id']);
        };

        $params = [
            'company_id' => $ca_id,
            'user_id' => $receiver_id,
            //'user_id' => 38, // temporary1
            'category_id' => FreshOfficeAPI::TASK_CATEGORY_СТАНДАРТНАЯ,
            'status_id' => FreshOfficeAPI::TASKS_STATUS_ЗАПЛАНИРОВАН,
            'type_id' => FreshOfficeAPI::TASK_TYPE_ОБРАЩЕНИЕ,
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
     * Выполняет создание нового контрагента через API Fresh Office.
     * @param $appeal Appeals обращение
     * @param $responsible_id integer идентификатор назначаемого ответственного
     * @return array|integer|bool
     */
    public static function foapi_createNewCounteragent($appeal, $responsible_id)
    {
        $contacts = [
            'first_name' => $appeal->form_username,
            'last_name' => $appeal->form_username,
            'post' => 'Представитель',
            'status_id' => FreshOfficeAPI::CONTACT_PERSON_STATE_РАБОТАЕТ,
        ];

        // дополним контакты номером телефона, если он задан
        if ($appeal->form_phone != null && $appeal->form_phone != '')
            $contacts['phones'][] = [
                'phone' => $appeal->form_phone,
            ];

        // дополним контакты электронным ящиком, если он задан
        if ($appeal->form_email != null && $appeal->form_email != '')
            $contacts['emails'][] = [
                'email' => $appeal->form_email,
            ];

        $params = [
            'name' => $appeal->form_company,
            'person' => FreshOfficeAPI::COMPANY_TYPE_ЮРЛИЦО,
            'type_id' => FreshOfficeAPI::COMPANY_STATE_НОВАЯ_КОМПАНИЯ,
            'group_id' => FreshOfficeAPI::COMPANY_GROUP_ОТДЕЛ_ВХОДЯЩИХ_ЗАЯВОК,
            'user_id' => $responsible_id,
            //'user_id' => 38, // temporary1
            'created' => date('Y-m-d\TH:i:s.u', time()),
            'created_by' => 'Веб-приложение',
        ];
        $params['requisites_legal'][] = [
            'short_name' => $appeal->form_company,
            'full_name' => $appeal->form_company,
        ];
        $params['contacts'][] = $contacts;
        $params['tasks'][] = [
            'user_id' => $responsible_id,
            //'user_id' => 38, // temporary1
            'category_id' => FreshOfficeAPI::TASK_CATEGORY_СТАНДАРТНАЯ,
            'status_id' => FreshOfficeAPI::TASKS_STATUS_ЗАПЛАНИРОВАН,
            'type_id' => FreshOfficeAPI::TASK_TYPE_ВХОДЯЩИЙ,
            'date_from' => date('Y-m-d\TH:i:s.u', time()),
            'date_till' => date('Y-m-d\TH:i:s.u', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"))),
            'note' => $appeal->form_message,
        ];

        //var_dump(json_encode($params));
        $response = FreshOfficeAPI::makePostRequestToApi('companies', $params);
        //var_dump($response);
        // проанализируем результат, который возвращает API Fresh Office
        $decoded_response = json_decode($response, true);
        if (isset($decoded_response['error'])) {
            $inner_message = '';
            if (isset($decoded_response['error']['innererror']))
                $inner_message = ' ' . $decoded_response['error']['innererror']['message'];
            // возникла ошибка при выполнении
            return 'При создании контрагента возникла ошибка: ' . $decoded_response['error']['message']['value'] . $inner_message;
        }
        elseif (isset($decoded_response['d']))
            // фиксируем идентификатор контрагента, который был успешно создан
            return $decoded_response['d']['id'];

        return false;
    }

    /**
     * Выполняет создание контрагента и задачи ответственному менеджеру.
     * @param $appeal Appeals обращение
     * @param $receiver_id integer идентификатор менеджера-получателя контрагента
     * @return array|bool
     */
    public static function createCounteragent($appeal, $receiver_id)
    {
        $errors = [];

        $foapi_result = self::foapi_createNewCounteragent($appeal, $receiver_id);
        if ($foapi_result !== false)
            if (!is_numeric($foapi_result))
                $errors[] = $foapi_result;
            else {
                // вот здесь идентификатор созданной задачи
                // это означает успех
                $appeal->fo_id_company = $foapi_result;
                $appeal->fo_company_name = $appeal->form_company;
                $appeal->fo_id_manager = $receiver_id;
            }
        else
            $errors[] = 'Не удалось создать контрагента по неизвестной причине';

        // если есть ошибки, возвращаем их
        if (count($errors) > 0) return $errors;

        // если ошибок нет, изменим статус обращения на "Ожидает оплаты"
        $appeal->ca_state_id = self::CA_STATE_NEW;
        $appeal->state_id = self::APPEAL_STATE_PAYMENT;
        if (!$appeal->save()) return ['Не удалось изменить статус обращения!'];

        // если абсолютно все действия выполнены, возвращаем успех
        return true;
    }

    /**
     * Выполняет передачу контрагента от одного менеджера к другому.
     * При этом выполняется соответствующий update-запрос к базе данных SQL.
     * Также выполняется создание сообщения пользователю, которому передан менеджер.
     * И создается задача новому менеджеру в статусе Напоминание о том, что ему передан контрагент.
     * @param $appeal Appeals обращение
     * @param $ca_id integer идентификатор контрагента, который передается
     * @param $sender_id integer идентификатор старого менеджера контрагента
     * @param $receiver_id integer идентификатор менеджера-получателя контрагента
     * @param $message string текст сообщения, которое будет отправлено новому менеджеру
     * @return array|bool
     */
    public static function delegateCounteragent($appeal, $ca_id, $sender_id, $receiver_id, $message)
    {
        $errors = [];

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // ПЕРЕДАЧА КОНТРАГЕНТА ДРУГОМУ МЕНЕДЖЕРУ
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $rows_affected = DirectMSSQLQueries::changeResponsible($ca_id, $receiver_id);
        if ($rows_affected == 0)
            $errors[] = 'Не удалось передать контрагента другому менеджеру';

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // СОЗДАНИЕ СООБЩЕНИЯ ПОЛЬЗОВАТЕЛЮ
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // отключено по желанию заказчика
        // чтобы включить, раскомментировать:
        //$foapi_result = self::foapi_createNewMessageForManager($sender_id, $receiver_id, $message);
        // чтобы включить, удалить строку (последнее действие, больше ничего не надо):
        $foapi_result = 0;
        if ($foapi_result !== false)
            if (!is_numeric($foapi_result))
                $errors[] = $foapi_result;
            else
                // вот здесь идентификатор созданного сообщения
                // это означает успех
                ;
        else
            $errors[] = 'Не удалось создать сообщение по неизвестной причине';
        unset($foapi_result);

        // второй вариант отправки сообщения
        // создание напрямую в базу (требуются права на запись в базу):
        //DirectMSSQLQueries::createNewMessageForManager($sender_id, $receiver_id, $message);

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // СОЗДАНИЕ ЗАДАЧИ ПОЛЬЗОВАТЕЛЮ
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $foapi_result = self::foapi_createNewTaskForManager($ca_id, $receiver_id, $appeal->form_message);
        if ($foapi_result !== false)
            if (!is_numeric($foapi_result))
                $errors[] = $foapi_result;
            else
                // вот здесь идентификатор созданной задачи
                // это означает успех
                ;
        else
            $errors[] = 'Не удалось создать задачу по неизвестной причине';

        // если есть ошибки, возвращаем их
        if (count($errors) > 0) return $errors;

        // если ошибок нет, изменим статус обращения на "Ожидает оплаты"
        $appeal->state_id = self::APPEAL_STATE_PAYMENT;
        if (!$appeal->save()) return ['Не удалось изменить статус обращения!'];

        // если абсолютно все действия выполнены, возвращаем успех
        return true;
    }

    /**
     * Получает данные менеджера по переданному в параметрах идентификатору или по имеющемуся значению в модели.
     * @param $id integer
     * @return array|bool
     */
    public function getResponsible($id = null)
    {
        if ($id == null)
            $manager_id = $this->fo_id_manager;
        else
            $manager_id = $id;

        $query_text = '
SELECT ID_MANAGER AS id, MANAGER_NAME AS name, e_mail AS email
FROM [CBaseCRM_Fresh_7x].[dbo].[MANAGERS]
WHERE ID_MANAGER = ' . $manager_id;

        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        if (count($result) > 0)
            return $result[0];
        else
            return false;
    }

    /**
     * Получает текущего ответственного по контрагенту из базы данных CRM.
     * @return array
     */
    public function getCounteragentsReliable()
    {
        $query_text = '
SELECT MANAGERS.ID_MANAGER AS id, MANAGERS.MANAGER_NAME AS name, MANAGERS.e_mail AS email
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
WHERE COMPANY.ID_COMPANY = ' . $this->fo_id_company;
        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Возвращает идентификатор или наименование ответственного в зависимости от значения параметра.
     * @param $field string
     * @return string
     */
    public function getCounteragentsReliableField($field = 'id')
    {
        $sourceTable = $this->getCounteragentsReliable();
        if (count($sourceTable) > 0)
            return $sourceTable[0][$field];
        else
            return '';
    }

    /**
     * Возвращает наименование статуса обращения.
     * @return string
     */
    public function getAppealStateName()
    {
        if (null === $this->state_id) {
            return '<не определен>';
        }

        $sourceTable = self::fetchAppealStates();
        $key = array_search($this->state_id, array_column($sourceTable, 'id'));
        if (false !== $key)
            return $sourceTable[$key]['name'];
        else
            return '';
    }

    /**
     * Возвращает наименование статуса клиента.
     * @param $state_id integer|null идентификатор статуса, для которого нужно определить наименование
     * @return string
     */
    public function getCaStateName($state_id = null)
    {
        // если не передается снаружи, возьмем значение поля модели
        if ($state_id === null) {
            $state_id = $this->ca_state_id;
        }

        if (null === $state_id) {
            return '<не определен>';
        }

        $sourceTable = self::fetchCaStates();
        $key = array_search($state_id, array_column($sourceTable, 'id'));
        if (false !== $key)
            return $sourceTable[$key]['name'];
        else
            return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAs()
    {
        return $this->hasOne(AppealSources::className(), ['id' => 'as_id']);
    }

    /**
     * Возвращает наименование источника обращения.
     * @return string
     */
    public function getAppealSourceName()
    {
        return $this->as == null ? '' : $this->as->name;
    }
}
