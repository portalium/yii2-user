<?php

namespace portalium\user\bundles;

use yii\web\AssetBundle;

class UserDeleteAsset extends AssetBundle
{
    public $sourcePath = '@vendor/portalium/yii2-user/src/assets';
    public $js = [
        'js/user-delete.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'portalium\theme\bundles\AppAsset',
    ];
    public $jsOptions = [
        'appendTimestamp' => true,
    ];
}
