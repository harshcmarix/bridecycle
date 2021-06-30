<?php

namespace app\modules\admin\assets;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/dist';
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'theme/admin/css/bootstrap3-wysihtml5.min.css',
        'theme/admin/css/addbtn.css',
        'theme/admin/css/custom.css',
    ];
    public $js = [
        'theme/admin/js/bootstrap3-wysihtml5.all.min.js',
        'theme/admin/js/JqueryValidation.js',
        'theme/admin/js/jquery-ui.js',
        'theme/admin/js/custom.js',
    ];
    public $depends = [
        'rmrevin\yii\fontawesome\AssetBundle',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

    public $jsOptions = ['position' => \yii\web\View::POS_HEAD];


    public function init()
    {
        parent::init();
    }
}