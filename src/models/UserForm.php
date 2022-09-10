<?php

namespace portalium\user\models;

use yii\base\Model;
use portalium\user\Module;
use portalium\user\models\User;

/**
 * This is the registration form model class for `\portalium\user\models\User` model.
 *
 */
class UserForm extends Model
{
    /**
     * @var string
     */
    public $first_name;

    /**
     * @var string
     */
    public $last_name;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    public $isNewRecord = true;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'first_name', 'last_name'], 'trim'],
            [['username', 'first_name', 'last_name'], 'required'],
            ['username', 'unique', 'targetClass' => '\portalium\user\models\User', 'message' => Module::t('This username has already been taken.')],
            [['username', 'first_name', 'last_name'], 'string', 'min' => 2, 'max' => 255],
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\portalium\user\models\User', 'message' => Module::t('This email address has already been taken.')],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
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
        if (!$this->validate()) {
            return null;
        }
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

    public function isNewRecord()
    {
        return $this->isNewRecord;
    }
}
