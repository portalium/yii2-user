<?php

namespace portalium\user\models;

use yii\base\BaseObject;
use yii\base\Model;
use portalium\user\Module;
use portalium\user\models\User;

class ImportForm extends Model
{

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
            'file' => Module::t('File')
        ];
    }


}
