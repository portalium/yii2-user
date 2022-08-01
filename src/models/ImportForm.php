<?php

namespace portalium\user\models;

use yii\base\Model;
use portalium\user\Module;

class ImportForm extends Model
{

    public $file;
    public $first_name;
    public $last_name;
    public $username;
    public $email;
    public $password;
    public $group;
    public $role;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv'],
            [['username', 'email'], 'required'],
            ['email', 'required'],
            [['username', 'group', 'role'], 'string', 'max' => 40],
            ['password', 'string', 'max' => 40],
            ['first_name', 'string', 'max' => 40],
            ['last_name', 'string', 'max' => 40],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'file' => Module::t('File'),
            'first_name' => Module::t('First Name Column Name'),
            'last_name' => Module::t('Last Name Column Name'),
            'username' => Module::t('Username Column Name'),
            'email' => Module::t('Email Column Name'),
            'password' => Module::t('Password Column Name')
        ];
    }

}
