<?php

namespace portalium\user\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use portalium\user\Module;

/**
 * This is the model class for table "user_group".
 *
 * @property int $id_user
 * @property int $id_user
 * @property int $id_group
 * @property int $date_create
 *
 * @property Group $group
 * @property User $user
 */
class UserGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{' . Module::$tablePrefix . 'user_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'date_create',
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_user', 'id_group'], 'required'],
            [['id_user', 'id_group'], 'integer'],
            [['id_group'], 'exist', 'skipOnError' => true, 'targetClass' => Group::class, 'targetAttribute' => ['id_group' => 'id_group']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['id_user' => 'id_group']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_user' => 'ID',
            'id_user' => 'User ID',
            'id_group' => 'Group ID',
            'date_create' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id_group' => 'id_group']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id_user' => 'id_user']);
    }
}
