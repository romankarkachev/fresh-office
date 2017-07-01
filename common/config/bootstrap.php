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

Yii::setAlias('uploads-appeals', '/uploads/appeals/'); // файлы к обращениям, относительный путь
Yii::setAlias('uploads-ferrymen-transport', '/uploads/ferrymen-transport/'); // файлы к автомобилям перевозчиков, относительный путь
