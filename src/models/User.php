<?php

namespace portalium\user\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use portalium\user\Module;
use portalium\base\Event;
use portalium\site\models\Setting;

class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{' . Module::$tablePrefix . 'user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    public function init()
    {
        $this->on(self::EVENT_AFTER_INSERT, function($event) {
            \Yii::$app->trigger(Module::EVENT_USER_CREATE, new Event(['payload' => $event->data]));
        }, $this);

        $this->on(self::EVENT_AFTER_UPDATE, function($event) {
            \Yii::$app->trigger(Module::EVENT_USER_UPDATE, new Event(['payload' => $event->data]));
        }, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'username', 'email'], 'safe'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
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
            'password' => Module::t('Password'),
            'status' => Module::t('Status'),
        ];
    }
    
    /**
     * Returns relational groups data.
     * @return \use yii\db\ActiveQuery;
     */
    public function getGroups()
    {
        return $this->hasMany(Group::class, ['id_group' => 'id_group'])
            ->viaTable(UserGroup::getTableSchema()->fullName, ['id_user' => 'id_user']);
    }

    public static function findIdentity($id_user)
    {
        return static::findOne(['id_user' => $id_user, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = 3600;
        return $timestamp + $expire >= time();
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
}
