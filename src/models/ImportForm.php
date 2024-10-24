<?php

namespace portalium\user\models;

use yii\base\Model;
use portalium\user\Module;

class ImportForm extends Model
{

    public $file;
    public $group;
    public $role;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv'],
            [['group', 'role'], 'string', 'max' => 40],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'file' => Module::t('File'),
        ];
    }

}
