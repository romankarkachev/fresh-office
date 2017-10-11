<?php
Yii::setAlias('webroot', $_SERVER['DOCUMENT_ROOT']);
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
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

Yii::setAlias('@uploads-transport-requests-fs', '@backend/../uploads/transport-requests'); // файлы к запросам на транспорт, полный путь

Yii::setAlias('uploads-appeals', '/uploads/appeals/'); // файлы к обращениям, относительный путь
Yii::setAlias('uploads-ferrymen-transport', '/uploads/ferrymen-transport/'); // файлы к автомобилям перевозчиков, относительный путь
Yii::setAlias('uploads-ferrymen', '/uploads/ferrymen/'); // файлы к перевозчикам, относительный путь
