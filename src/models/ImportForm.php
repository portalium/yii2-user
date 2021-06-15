<?php

namespace portalium\user\models;

use yii\base\BaseObject;
use yii\base\Model;
use portalium\user\Module;
use portalium\user\models\User;

class ImportForm extends Model
{
    public $first_name;
    public $last_name;
    public $username;
    public $email;
    public $password;
    public $file;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'first_name' => Module::t('First Name'),
            'last_name' => Module::t('Last Name'),
            'username' => Module::t('Username'),
            'email' => Module::t('Email'),
            'password' => Module::t('Password')
        ];
    }

    /**
     * Creates new user and if success returns `\portalium\user\models\User` model.
     * @return \portalium\user\models\User|null
     */
    public function createUser()
    {
        $user = new User();
        $user->first_name = $this->first_name;
        $user->last_name = $this->last_name;
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->access_token = \Yii::$app->security->generateRandomString();
        $user->generateAuthKey();
        return $user->save() ? $user : null;
    }
}
