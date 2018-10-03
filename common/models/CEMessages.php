<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ce_messages".
 *
 * @property integer $id
 * @property integer $detected_at
 * @property integer $obtained_at
 * @property integer $mailbox_id
 * @property string $folder_tech
 * @property string $folder_name
 * @property integer $uid
 * @property string $subject
 * @property string $body_text
 * @property string $body_html
 * @property integer $attachment_count
 * @property string $header
 * @property integer $created_at
 * @property integer $is_complete
 *
 * @property string $fromName
 * @property string $fromEmail
 * @property string $fromHtmlRep
 * @property string $fromRep
 * @property string $fromRepForTitle
 *
 * @property CEAddresses[] $addresses
 * @property CEAttachedFiles[] $attachedFiles
 * @property CEAddresses $fromBlock
 * @property CEMailboxes $mailbox
 */
class CEMessages extends \yii\db\ActiveRecord
{
    /**
     * Message const
     *
     * @const integer   TYPE_TEXT
     * @const integer   TYPE_MULTIPART
     *
     * @const integer   ENC_7BIT
     * @const integer   ENC_8BIT
     * @const integer   ENC_BINARY
     * @const integer   ENC_BASE64
     * @const integer   ENC_QUOTED_PRINTABLE
     * @const integer   ENC_OTHER
     */
    const TYPE_TEXT = 0;
    const TYPE_MULTIPART = 1;
    const ENC_7BIT = 0;
    const ENC_8BIT = 1;
    const ENC_BINARY = 2;
    const ENC_BASE64 = 3;
    const ENC_QUOTED_PRINTABLE = 4;
    const ENC_OTHER = 5;

    /**
     * @var string все адреса, связанные с письмом, строкой
     */
    public $addressesLinear;

    /**
     * @var string все имена файлов, вложенные в письмо, строкой
     */
    public $attachedFilenamesLinear;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ce_messages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mailbox_id', 'uid'], 'required'],
            [['detected_at', 'obtained_at', 'mailbox_id', 'uid', 'attachment_count', 'created_at', 'is_complete'], 'integer'],
            [['body_text', 'body_html', 'header'], 'string'],
            [['folder_tech', 'folder_name', 'subject'], 'string', 'max' => 255],
            [['mailbox_id'], 'exist', 'skipOnError' => true, 'targetClass' => CEMailboxes::className(), 'targetAttribute' => ['mailbox_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'detected_at' => 'Дата и время постановки в очередь письма',
            'obtained_at' => 'Дата и время скачивания письма в систему',
            'mailbox_id' => 'Почтовый ящик',
            'folder_tech' => 'Папка (техническое название)',
            'folder_name' => 'Папка',
            'uid' => 'Уникальный идентификатор письма в почтовом ящике',
            'subject' => 'Тема письма',
            'body_text' => 'Текст письма в plain-виде',
            'body_html' => 'Текст письма в html-коде',
            'attachment_count' => 'Количество вложений письма',
            'header' => 'Технический заголовок письма',
            'created_at' => 'Дата и время создания письма',
            'is_complete' => 'Скачано ли письмо полностью',
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
                    ActiveRecord::EVENT_BEFORE_INSERT => ['detected_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['obtained_at'],
                ],
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

            // удаляем информацию о приаттаченных файлах
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $records = CEAttachedFiles::find()->where(['message_id' => $this->id])->all();
            foreach ($records as $record) $record->delete();

            // удаляем адреса
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $records = CEAddresses::find()->where(['message_id' => $this->id])->all();
            foreach ($records as $record) $record->delete();

            return true;
        }

        return false;
    }

    /**
     * Получение заголовка письма.
     */
    public static function getHeaderRaw($mbox, $uid)
    {
        return imap_fetchbody($mbox, $uid, '0', FT_UID);
    }

    /**
     * Получение заголовка письма в виде объекта.
     */
    public static function getHeader($mbox, $uid)
    {
        try {
            $thisHeader = imap_fetchheader($mbox, $uid, FT_UID);
            if ($thisHeader) {
                return imap_rfc822_parse_headers($thisHeader);
            }
            //return imap_rfc822_parse_headers(self::getHeaderRaw($mbox, $uid));
        }
        catch (\Exception $exception) {return false;}
    }

    /**
     * Раскодировка некоего текста.
     * @param $text string строка в формате base64 для imap
     * @return string
     */
    public static function getDecodedHeader($text)
    {
        // imap_mime_header_decode - декодирует элементы MIME-шапки в виде массива
        // у каждого элемента указана кодировка(charset) и сам текст(text)
        $elements = imap_mime_header_decode($text);
        $ret = "";
        // перебираем элементы
        for ($i=0; $i<count($elements); $i++) {
            $charset = $elements[$i]->charset;//кодировка
            $text = $elements[$i]->text;//закодированный текст
            if ($charset == 'default') {
                // если элемент не кодирован, то значение кодировки default
                $ret .= $text;
            } else {
                // приводим всё кодировке к UTF-8
                $ret .= iconv($charset,"UTF-8",$text);
            }
        }

        return $ret;
    }

    /**
     * Заполняем ассоциативный массив, где ключом является тип адреса, а значение - массив адресов.
     * @param $header
     * @param $type
     * @param $map array
     * @return array
     */
    public static function getAddress($header, $type, $map)
    {
        $result = $map;
        // проверка существования типа в заголовке
        if (property_exists($header, $type)) {
            $arr = $header->$type;
            if (is_array($arr) && count($arr) > 0) {
                $result[$type] = $arr;
            }
        }

        return $result;
    }

    /**
     * Подсчет количества вложений.
     * @param $mbox
     * @param $uid
     * @param $message_id integer идентификатор письма в нашей базе
     * @return int
     */
    public static function countAttaches($mbox, $uid, $message_id)
    {
        //получаем структуру сообщения
        try {
            $struct = imap_fetchstructure($mbox, $uid, FT_UID);
        }
        catch (\Exception $exception) {return -1;}
        $attachCount = 0;
        if (!$struct->parts) return $attachCount;
        //перебираем части сообщения
        foreach ($struct->parts as $number => $part) {
            //ищем части, у которых ifdisposition равно 1 и disposition равно ATTACHMENT,
            //все остальные части игнорируем. Также стоит заметить, что значение поля
            //disposition может быть как в верхнем, так и в нижнем регистрах,
            //т.е. может быть "attachment" и "ATTACHMENT". Поэтому в коде всё приведено
            //к верхнему регистру
            if(!$part->ifdisposition || strtoupper($part->disposition) != "ATTACHMENT") continue;

            //получаем название файла
            $filename = self::getDecodedHeader($part->dparameters[0]->value);
            //получаем содержимое файла в закодированном виде
            $text = imap_fetchbody($mbox, $uid, $number + 1, FT_UID);

            // https://mail.yandex.ru/message_part/%D0%B3%D0%BE%D1%81%D0%BF%D0%BE%D1%88%D0%BB%D0%B8%D0%BD%D0%B0%20%D0%B7%D0%B0%20%D0%B2%D1%8B%D0%B1%D1%80%D0%BE%D1%81.doc?_uid=378401314&hid=1.2&ids=137359788634821984&name=%D0%B3%D0%BE%D1%81%D0%BF%D0%BE%D1%88%D0%BB%D0%B8%D0%BD%D0%B0%20%D0%B7%D0%B0%20%D0%B2%D1%8B%D0%B1%D1%80%D0%BE%D1%81.doc
            $model = new CEAttachedFiles([
                'message_id' => $message_id,
                'ofn' => $filename,
                'size' => strlen($text),
            ]);

            $model->save();
            $attachCount++;
        }

        return $attachCount;
    }

    /**
     * Decode a given string
     *
     * @param $string
     * @param $encoding
     *
     * @return string
     */
    public function decodeString($string, $encoding) {
        switch ($encoding) {
            case self::ENC_7BIT:
                return $string;
            case self::ENC_8BIT:
                return quoted_printable_decode(imap_8bit($string));
            case self::ENC_BINARY:
                return imap_binary($string);
            case self::ENC_BASE64:
                return imap_base64($string);
            case self::ENC_QUOTED_PRINTABLE:
                return quoted_printable_decode($string);
            case self::ENC_OTHER:
                return $string;
            default:
                return $string;
        }
    }

    /**
     * Convert the encoding
     *
     * @param $str
     * @param string $from
     * @param string $to
     *
     * @return mixed|string
     */
    private function convertEncoding($str, $from = "ISO-8859-2", $to = "UTF-8") {
        if (function_exists('iconv') && $from != 'UTF-7' && $to != 'UTF-7') {
            return iconv(CEEncodingAliases::get($from), $to.'//IGNORE', $str);
        } else {
            if (!$from) {
                return mb_convert_encoding($str, $to);
            }
            return mb_convert_encoding($str, $to, $from);
        }
    }

    /**
     * Get the encoding of a given abject
     *
     * @param object $structure
     *
     * @return null|string
     */
    private function getEncoding($structure) {
        if (property_exists($structure, 'parameters')) {
            foreach ($structure->parameters as $parameter) {
                if (strtolower($parameter->attribute) == "charset") {
                    return strtoupper($parameter->value);
                }
            }
        }
        return null;
    }

    /**
     * Parse the Message body
     *
     * @return $this
     */
    public function parseBody($connection, $muid) {
        $structure = imap_fetchstructure($connection, $muid, FT_UID);
        $this->fetchStructure($connection, $muid, $structure);
        return $this;
    }

    /**
     * Fetch the Message structure
     *
     * @param $structure
     * @param mixed $partNumber
     */
    private function fetchStructure($connection, $muid, $structure, $partNumber = null) {
        if ($structure->type == self::TYPE_TEXT &&
            ($structure->ifdisposition == 0 ||
                ($structure->ifdisposition == 1 && !isset($structure->parts) && $partNumber == null)
            )
        ) {
            if ($structure->subtype == "PLAIN") {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                $encoding = $this->getEncoding($structure);
                $content = imap_fetchbody($connection, $muid, $partNumber, FT_UID);
                $content = $this->decodeString($content, $structure->encoding);
                $content = $this->convertEncoding($content, $encoding);
                $this->body_text = $content;
                //$this->fetchAttachment($structure, $partNumber);
            } elseif ($structure->subtype == "HTML") {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                $encoding = $this->getEncoding($structure);
                $content = imap_fetchbody($connection, $muid, $partNumber, FT_UID);
                $content = $this->decodeString($content, $structure->encoding);
                $content = $this->convertEncoding($content, $encoding);
                $this->body_html = $content;
            }
        } elseif ($structure->type == self::TYPE_MULTIPART) {
            foreach ($structure->parts as $index => $subStruct) {
                $prefix = "";
                if ($partNumber) {
                    $prefix = $partNumber.".";
                }
                $this->fetchStructure($connection, $muid, $subStruct, $prefix.($index + 1));
            }
        } else {
            /*
            if ($this->getFetchAttachmentOption() === true) {
                $this->fetchAttachment($structure, $partNumber);
            }
            */
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasMany(CEAddresses::className(), ['message_id' => 'id']);
    }

    /**
     * Блок "От", содержащий информацию об отправителе.
     * @return \yii\db\ActiveQuery
     */
    public function getFromBlock()
    {
        return $this->hasOne(CEAddresses::className(), ['message_id' => 'id'])->onCondition(['type' => 'from']);
    }

    /**
     * Возвращает наименование отправителя.
     * @return string
     */
    public function getFromName()
    {
        return (!empty($this->fromBlock) ? $this->fromBlock->name : '');
    }

    /**
     * Возвращает наименование отправителя.
     * @return string
     */
    public function getFromEmail()
    {
        return (!empty($this->fromBlock) ? $this->fromBlock->email : '');
    }

    /**
     * Возвращает наименование отправителя и его электронный ящик в html-формате.
     * @return string
     */
    public function getFromHtmlRep()
    {
        $senderRep = '';
        if (!empty($this->fromBlock)) {
            if (!empty($this->fromName)) $senderRep .= $this->fromName;
            if (!empty($this->fromEmail)) $senderRep .= ' <span class="text-muted">&lt' . $this->fromEmail . '&gt;</span>';
        }

        return $senderRep;
    }

    /**
     * Возвращает наименование отправителя и его электронный ящик.
     * @return string
     */
    public function getFromRep()
    {
        $senderRep = '';
        if (!empty($this->fromBlock)) {
            if (!empty($this->fromName)) $senderRep .= $this->fromName;
            if (!empty($this->fromEmail)) $senderRep .= ' &lt;' . $this->fromEmail . '&gt;';
        }

        return $senderRep;
    }

    /**
     * Возвращает наименование отправителя и его электронный ящик.
     * @return string
     */
    public function getFromRepForTitle()
    {
        $senderRep = '';
        if (!empty($this->fromBlock)) {
            if (!empty($this->fromName)) $senderRep .= $this->fromName;
            if (!empty($this->fromEmail)) $senderRep .= ' (' . $this->fromEmail . ')';
        }

        return $senderRep;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailbox()
    {
        return $this->hasOne(CEMailboxes::className(), ['id' => 'mailbox_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachedFiles()
    {
        return $this->hasMany(CEAttachedFiles::className(), ['message_id' => 'id']);
    }
}
