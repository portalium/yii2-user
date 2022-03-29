<?php

namespace portalium\user\models;

use Yii;
use yii\base\Model;
use portalium\user\Module;


/**
 * This is the registration form model class for `\portalium\user\models\User` model.
 *
 */
class ModuleForm extends Model
{
    /**
     * @var string
     */
    public $modules = [];
    public $default_user;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['modules'], 'safe'],
            [['default_user'], 'integer'],
            //array
            [['modules'], 'each'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'modules' => Module::t('Modules'),
            'defaultUser' => Module::t('Default User'),
        ];
    }

}
