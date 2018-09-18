<?php

namespace cemail\controllers;

use Yii;
use common\models\CEAddresses;
use common\models\CEMailboxes;
use common\models\CEMessages;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * Default controller
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'login', 'error', 'open',
                            'calc-mailboxes-messages-count',
                            'primary-fetching-messages-headers', 'fetch-new-messages-headers', 'obtain-incomplete-messages'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($action->id == 'error') $this->layout = '//na';

            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Отображает Рабочий стол.
     * @return mixed
     */
    public function actionIndex()
    {
        $totalLettersCount = CEMessages::find()->where(['is_complete' => true])->count();
        return $this->render('index', ['totalLettersCount' => $totalLettersCount]);
    }

    /**
     * Возвращает массив с количеством писем в каждом отдельном почтовом ящике.
     * calc-mailboxes-messages-count
     */
    public function actionCalcMailboxesMessagesCount()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return CEMessages::find()->select([
            'messagesCount' => 'COUNT(*)',
            'mailbox_id',
            'is_active',
        ])
            ->leftJoin(CEMailboxes::tableName(), CEMailboxes::tableName() . '.id = ' . CEMessages::tableName() . '.mailbox_id')
            ->where(['is_complete' => true])
            ->groupBy('mailbox_id')->asArray()->all();
    }

    /**
     * Загружает заголовки писем, которые отсутствуют в нашей системе.
     * @return mixed
     */
    public function actionPrimaryFetchingMessagesHeaders()
    {
        $mailboxes = CEMailboxes::find()->where(['is_active' => true, 'is_primary_done' => 0])->limit(20)->all();
        foreach ($mailboxes as $mailbox) {
            /* @var $mailbox CEMailboxes */

            print '<p>Сбор из ящика ' . $mailbox->name . '.</p>';
            $ssl = $mailbox->is_ssl ? '/ssl' : '';
            $connectionString = "{{$mailbox->host}:{$mailbox->port}{$ssl}}";
            try {
                $imapResource = imap_open($connectionString, $mailbox->username, $mailbox->password, OP_READONLY);
            }
            catch (\Exception $exception) {
                print $exception->getMessage();
                $mailbox->is_primary_done = -1;
                $mailbox->save(false);
            }

            if (empty($imapResource)) {
                print '<p>Error opening IMAP. ' . imap_last_error() . '</p>';
                continue;
            }

            // выполним перебор всех папок в этом почтовом ящике и заберем из каждой письма
            try {$folders = imap_listmailbox($imapResource, $connectionString, '*');}
            catch (\Exception $exception) {print $exception->getMessage(); continue;}
            if ($folders == false) {
                echo "Call failed<br>\n";
            } else {
                $batchLetters = [];

                if (is_array($folders)) {
                    foreach ($folders as $folder) {
                        if (imap_reopen($imapResource, $folder, OP_READONLY)) {
                            // человекопонятное представление папки:
                            $folderTech = str_replace($connectionString, '', $folder);
                            $folderName = str_replace($connectionString, '', mb_convert_encoding($folder, 'UTF-8', 'UTF7-IMAP'));
                            if (strtolower($folderTech) == 'inbox') {
                                print '<p>INBOX detected!</p>';
                                $folderTech = null;
                                $folderName = null;
                            }

                            $arr = imap_search($imapResource, 'ALL', SE_UID);
                            if (!is_array($arr)) continue;

                            print '<p>Обработка пабки ' . $folderName . ' (' . $folderTech . '), сообщений: ' . count($arr) . '</p>';

                            //перебираем сообщения
                            foreach ($arr as $message_uid) {
                                // помещаем в массив писем, чтобы потом разом сохранить все письма
                                $batchLetters[] = [
                                    time(),
                                    $mailbox->id,
                                    $folderTech,
                                    $folderName,
                                    $message_uid,
                                ];
                            }

                            try {
                                Yii::$app->db->createCommand()->batchInsert(CEMessages::tableName(), ['detected_at', 'mailbox_id', 'folder_tech', 'folder_name', 'uid'], $batchLetters)->execute();
                                $batchLetters = [];
                            }
                            catch (\Exception $exception) {print $exception->getMessage();}
                        }
                        else {
                            print '<p>Почему-то не удалось открыть ящик.</p>';
                        }
                    }
                }
                else {
                    print '<p>Нет подпапок.</p>';
                }
            }

            $mailbox->is_primary_done = true;
            $mailbox->save(false);

            imap_close($imapResource);
        }
    }

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     * Загружает заголовки писем, которые отсутствуют в нашей системе.
     * @return mixed
     */
    public function actionFetchNewMessagesHeaders()
    {
        $mailboxes = CEMailboxes::findAll(['is_active' => true]);
        foreach ($mailboxes as $mailbox) {
            print '<p>Сбор из ящика ' . $mailbox->name . '.</p>';
            $ssl = $mailbox->is_ssl ? '/ssl' : '';
            $connectionString = "{{$mailbox->host}:{$mailbox->port}{$ssl}}";
            try {
                $imapResource = imap_open($connectionString, $mailbox->username, $mailbox->password, OP_READONLY);
            }
            catch (\Exception $exception) {print $exception->getMessage();}

            if (empty($imapResource)) {
                print '<p>Error opening IMAP. ' . imap_last_error() . '</p>';
                continue;
            }

            // выполним перебор всех папок в этом почтовом ящике и заберем из каждой письма
            try {$folders = imap_listmailbox($imapResource, $connectionString, '*');}
            catch (\Exception $exception) {print $exception->getMessage(); continue;}
            if ($folders == false) {
                echo "Call failed<br>\n";
            } else {
                $batchLetters = [];

                if (is_array($folders)) {
                    foreach ($folders as $folder) {
                        if (imap_reopen($imapResource, $folder, OP_READONLY)) {
                            // человекопонятное представление папки:
                            $folderTech = str_replace($connectionString, '', $folder);
                            $folderName = str_replace($connectionString, '', mb_convert_encoding($folder, 'UTF-8', 'UTF7-IMAP'));
                            if (strtolower($folderTech) == 'inbox') {
                                print '<p>INBOX detected!</p>';
                                $folderTech = null;
                                $folderName = null;
                            }
                            print '<p>Обработка пабки ' . $folderName . ' (' . $folderTech . ')</p>';

                            $lastId = CEMessages::find()->select('MAX(id)')->where(['mailbox_id' => $mailbox->id, 'folder_tech' => $folderTech])->scalar();
                            if (empty($lastId)) $lastId = 0; else $lastId++;

                            $uid_from = $lastId;
                            $uid_to = $lastId + 100;
                            $range = "$uid_from:$uid_to";
                            //print '<p>Выборка ' . $range . '</p>';
                            $arr = imap_fetch_overview($imapResource, $range, FT_UID);
                            //$arr = imap_search($imapResource, 'ALL', SE_UID);
                            if (!is_array($arr)) continue;

                            //перебираем сообщения
                            //foreach ($arr as $id) {
                            foreach ($arr as $obj) {
                                // получаем UID сообщения
                                $message_uid = $obj->uid;
                                //$message_uid = $obj;
                                print '<p>Оброботко сообщенее ' . $message_uid . '</p>';

                                // создаем запись в таблице messages,
                                // тем самым поставив сообщение в очередь на загрузку
                                $batchLetters[] = [
                                    time(),
                                    $mailbox->id,
                                    $folderTech,
                                    $folderName,
                                    $message_uid,
                                ];
                            }

                            try {
                                Yii::$app->db->createCommand()->batchInsert(CEMessages::tableName(), ['detected_at', 'mailbox_id', 'folder_tech', 'folder_name', 'uid'], $batchLetters)->execute();
                                $batchLetters = [];
                            }
                            catch (\Exception $exception) {print $exception->getMessage();}
                        }
                        else {
                            print '<p>Почему-то не удалось открыть ящик.</p>';
                        }
                    }
                }
                else {
                    print '<p>Нет подпапок.</p>';
                }
            }

            imap_close($imapResource);
        }
    }

    /**
     * Закачивает сообщения из очереди.
     */
    public function actionObtainIncompleteMessages()
    {
        $mailboxes = CEMailboxes::findAll(['is_active' => true, 'is_primary_done' => 1]);
        $iterator = 0;
        foreach($mailboxes as $mailbox) {
            $ssl = $mailbox->is_ssl ? '/ssl' : '';
            $connectionString = "{{$mailbox->host}:{$mailbox->port}{$ssl}}";
            $imapResource = imap_open($connectionString, $mailbox->username, $mailbox->password, OP_READONLY);
            if (!$imapResource) {
                print '<p>Error opening IMAP. ' . imap_last_error() . '</p>';
                continue;
            }

            $currentFolder = null;
            $messages = CEMessages::find()->where(['mailbox_id' => $mailbox->id, 'is_complete' => false])->orderBy('folder_tech')->limit(100)->all();
            foreach ($messages as $message) {
                /* @var $message CEMessages */

                $iterator++;

                if ($currentFolder != $message->folder_tech) {
                    if (!imap_reopen($imapResource, $connectionString . $message->folder_tech, OP_READONLY)) {
                        continue;
                    }
                    //print '<p>Открыто подключение к папке ' . $message->folder_name . '.</p>';
                }

                $currentFolder = $message->folder_tech;

                $header = CEMessages::getHeader($imapResource, $message->uid); // заголовок письма
                if ($header === false) {
                    // если заголовок письма получить не удалось, отметим это письмо и перейдем к следующему
                    $message->updateAttributes(['is_complete' => 2]);
                    continue;
                }

                //получение адресов из заголовка письма
                $addressMap = [];
                $address_types = ['to', 'from', 'reply_to', 'sender', 'cc', 'bcc'];
                foreach ($address_types as $address_type) {
                    $addressMap = CEMessages::getAddress($header, $address_type, $addressMap);
                }

                foreach ($addressMap as $key => $arr) {
                    foreach ($arr as $obj) {
                        $type = $key;
                        $address = "$obj->mailbox@$obj->host"; // склеиваем email
                        $addressModel = new CEAddresses([
                            'message_id' => $message->id,
                            'type' => $type,
                            'email' => $address,
                            'name' => CEMessages::getDecodedHeader($obj->personal),
                        ]);
                        $addressModel->save();
                    }
                }

                try {
                    $message->parseBody($imapResource, $message->uid);
                    $message->save();
                }
                catch (\Exception $exception) {print '<p>Провал при запросе сообщения ' . $message->uid . ' из папки ' . $message->folder_name . ': ' . $exception->getMessage() . '</p>';}

                $message->updateAttributes([
                    'obtained_at' => time(),
                    'subject' => CEMessages::getDecodedHeader($header->subject),
                    'header' => CEMessages::getHeaderRaw($imapResource, $message->uid), // технический заголовок письма
                    'created_at' => strtotime($header->date),
                    'attachment_count' => CEMessages::countAttaches($imapResource, $message->uid, $message->id),
                    'is_complete' => true,
                ]);

                if ($iterator >= 100) break 2;
            }

            imap_close($imapResource);
        }
    }
}
