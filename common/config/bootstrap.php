<?php
Yii::setAlias('webroot', $_SERVER['DOCUMENT_ROOT']);
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@uploads', dirname(dirname(__DIR__)) . '/uploads');
Yii::setAlias('@uploads-export-templates-fs', '@webroot/uploads/export-templates/');
Yii::setAlias('@uploads-documents-fs', '@webroot/uploads/documents/');
