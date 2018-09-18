<?php

namespace cemail\assets;

use yii\web\AssetBundle;

/**
 * Cemail application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/style.css',
    ];
    public $js = [
        // скрипт для подсчета количества писем во всех ящиках для вывода в сайдбаре, применяется для любой страницы
        'js/mbcb.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
