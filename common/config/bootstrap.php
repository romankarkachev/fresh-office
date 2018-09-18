<?php
Yii::setAlias('webroot', $_SERVER['DOCUMENT_ROOT']);
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@ferryman', dirname(dirname(__DIR__)) . '/ferryman');
Yii::setAlias('@customer', dirname(dirname(__DIR__)) . '/customer');
Yii::setAlias('@cemail', dirname(dirname(__DIR__)) . '/cemail');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@uploads', dirname(dirname(__DIR__)) . '/uploads');

Yii::setAlias('@uploads-export-templates-fs', '@webroot/uploads/export-templates/');
Yii::setAlias('@uploads-documents-fs', '@webroot/uploads/documents/');
Yii::setAlias('@uploads-appeals-fs', '@backend/../uploads/appeals'); // файлы к обращениям, полный путь
Yii::setAlias('@uploads-ferrymen-transport-fs', '@backend/../uploads/ferrymen-transport'); // файлы к автомобилям перевозчиков, полный путь
Yii::setAlias('@uploads-ferrymen-drivers-fs', '@backend/../uploads/ferrymen-drivers'); // файлы к водителям перевозчиков, полный путь
Yii::setAlias('@uploads-ferrymen-fs', '@backend/../uploads/ferrymen'); // файлы к перевозчикам, полный путь
Yii::setAlias('@uploads-temp-pdfs', '@backend/../uploads/temp-pdfs'); // проекты в формате PDF для рассылки
Yii::setAlias('@uploads-production-files-fs', '@backend/../uploads/production-files'); // файлы, которые прикрепляет производство
Yii::setAlias('@uploads-licenses-files-fs', '@backend/../uploads/licenses-files'); // файлы со сканами страниц лицензии
Yii::setAlias('@uploads-payment-orders-fs', '@backend/../uploads/payment-orders'); // файлы заявок на оплату
Yii::setAlias('@uploads-correspondence-packages-fs', '@backend/../uploads/correspondence-packages'); // файлы пакетов корреспонденции
Yii::setAlias('@uploads-transport-requests-fs', '@backend/../uploads/transport-requests'); // файлы к запросам на транспорт, полный путь
Yii::setAlias('@uploads-mail-extractions-fs', '@backend/../uploads/mail-extractions'); // файлы с выгрузкой корпоративной почты, полный путь

Yii::setAlias('uploads-appeals', '/uploads/appeals/'); // файлы к обращениям, относительный путь
Yii::setAlias('uploads-ferrymen-drivers', '/uploads/ferrymen-drivers/'); // файлы к автомобилям перевозчиков, относительный путь
Yii::setAlias('uploads-ferrymen-transport', '/uploads/ferrymen-transport/'); // файлы к автомобилям перевозчиков, относительный путь
Yii::setAlias('uploads-ferrymen', '/uploads/ferrymen/'); // файлы к перевозчикам, относительный путь
Yii::setAlias('uploads-payment-orders', '/uploads/payment-orders/'); // файлы к перевозчикам, относительный путь
Yii::setAlias('uploads-transport-requests', '/uploads/transport-requests/'); // файлы к запросам на транспорт, относительный путь
Yii::setAlias('uploads-correspondence-packages', '/uploads/correspondence-packages/'); // файлы пакетов корреспонденции, относительный путь
