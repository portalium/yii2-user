<?php

namespace portalium\user;

class Module extends \portalium\base\Module
{
    public $apiRules = [
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'user/users',
            ]
        ],
    ];
    
    public static function moduleInit()
    {
        self::registerTranslation('user','@portalium/user/messages',[
            'user' => 'user.php',
        ]);
    }

    public static function t($message, array $params = [])
    {
        return parent::coreT('user', $message, $params);
    }
}
