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
    const STATUS_PASSIVE = 20;

    const EMAIL_VERIFY = 10;
    const EMAIL_NOT_VERIFY = 20;

    const SMS_VERIFY = 10;
    const SMS_NOT_VERIFY = 20;

    const IS_VIRTUAL_FALSE = 0;
    const IS_VIRTUAL_TRUE = 1;

    private $_smsVerificationConfirmed = false;
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
                'createdAtAttribute' => 'date_create',
                'updatedAtAttribute' => 'date_update',
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    public function init()
    {
        $this->on(self::EVENT_AFTER_INSERT, function ($event) {
            \Yii::$app->trigger(Module::EVENT_USER_CREATE, new Event(['payload' => $event->data]));
            Event::trigger(Yii::$app->getModules(), Module::EVENT_USER_CREATE, new Event(['payload' => $event->data]));
        }, $this);
        
        /* $this->on(self::EVENT_BEFORE_DELETE, function ($event) {
            \Yii::$app->trigger(Module::EVENT_USER_DELETE_BEFORE, new Event(['payload' => $event->data]));
            Event::trigger(Yii::$app->getModules(), Module::EVENT_USER_DELETE_BEFORE, new Event(['payload' => $event->data]));
        }, $this); */

        $this->on(self::EVENT_AFTER_UPDATE, function ($event) {
            \Yii::$app->trigger(Module::EVENT_USER_UPDATE, new Event(['payload' => $event->data]));
        }, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'username', 'email', 'id_avatar'], 'safe'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED, self::STATUS_PASSIVE]],
            ['email_verify', 'default', 'value' => self::EMAIL_NOT_VERIFY],
            ['email_verify', 'in', 'range' => [self::EMAIL_VERIFY, self::EMAIL_NOT_VERIFY]],
            ['tel_no', 'string', 'max' => 16],
            ['tel_no', 'match', 'pattern' => '/^\+[1-9]\d{1,14}$/'],
            ['tel_no', 'unique'],
            ['sms_verified', 'default', 'value' => self::SMS_NOT_VERIFY],
            ['sms_verified', 'integer'],
            ['sms_verified', 'in', 'range' => [self::SMS_VERIFY, self::SMS_NOT_VERIFY]],
            ['id_avatar', 'integer'],
            ['is_virtual', 'default', 'value' => self::IS_VIRTUAL_FALSE],
            ['is_virtual', 'in', 'range' => [self::IS_VIRTUAL_FALSE, self::IS_VIRTUAL_TRUE]],
        ];
    }

    /**
     * Strips sensitive attributes from the JSON representation. access_token is kept only on
     * the current user's own record (login responses rely on it); exposing another user's
     * token would let it be used to impersonate that account.
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password_hash'], $fields['auth_key'], $fields['password_reset_token'], $fields['verification_token']);

        $currentId = Yii::$app->has('user') ? Yii::$app->user->id : null;
        if ($currentId === null || (int) $currentId !== (int) $this->id_user) {
            unset($fields['access_token'], $fields['tel_no']);
        }

        return $fields;
    }

    /**
     * REST `?expand=groups` returns the user's groups.
     */
    public function extraFields()
    {
        return ['groups'];
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
            'tel_no' => Module::t('Telephone Number'),
            'password' => Module::t('Password'),
            'sms_verified' => Module::t('SMS Verified'),
            'status' => Module::t('Status'),
            'date_create' => Module::t('Date Create'),
            'is_virtual' => Module::t('Virtual'),
        ];
    }

    /**
     * @deprecated Use getStatusList() method when you need to retrieve a list of statuses for dropdowns, filters, or any other scenario 
     * where a list of all possible statuses is required.
     */
    public static function getStatus()
    {
        return [
            self::STATUS_ACTIVE => Module::t('Active'),
            self::STATUS_DELETED => Module::t('Deleted'),
            self::STATUS_PASSIVE => Module::t('Passive'),
        ];
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => Module::t('Active'),
            self::STATUS_DELETED => Module::t('Deleted'),
            self::STATUS_PASSIVE => Module::t('Passive'),
        ];
    }

    public static function getEmailVerify()
    {
        return [
            self::EMAIL_VERIFY => Module::t('Email Verify'),
            self::EMAIL_NOT_VERIFY => Module::t('Email Not Verify'),
        ];
    }

    public static function getSmsVerify()
    {
        return [
            self::SMS_VERIFY => Module::t('SMS Verified'),
            self::SMS_NOT_VERIFY => Module::t('SMS Not Verified'),
        ];
    }

    public static function getIsVirtualList()
    {
        return [
            self::IS_VIRTUAL_FALSE => Module::t('Real'),
            self::IS_VIRTUAL_TRUE => Module::t('Virtual'),
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

    /**
     * Only an active account's API key is accepted; a passive or deleted account's key no longer works.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (!is_string($token) || $token === '') {
            return null;
        }

        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
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

    public static function findByVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token
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

    public function getAuthor(){
        return json_encode([
            'id_user'=>$this->id_user,
            'first_name'=>$this->first_name,
            'last_name'=>$this->last_name,
            'username'=>$this->username,
            'email'=>$this->email
        ]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Get current user's email
     * @return string|null User email or null if not set
     */
    public function getEmail()
    {
        return $this->email;
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

    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
    
    /**
     * When an account is moved to passive or deleted, its email verification token is cleared.
     * Otherwise an account an admin closed could reopen itself via the old verification link or
     * "resend verification". The access token is also replaced with a value nobody knows (the
     * column is NOT NULL and UNIQUE, so it can't be set to NULL); reopening the account does not
     * bring the old token back, the user gets a valid one at login. If the status didn't actually
     * change (the form always submits a status value), both tokens are left alone.
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $closed = in_array((int) $this->status, [self::STATUS_PASSIVE, self::STATUS_DELETED], true);
        if (!$insert && $closed && (int) $this->getOldAttribute('status') !== (int) $this->status) {
            $this->verification_token = null;
            $this->access_token = Yii::$app->security->generateRandomString(40);
        }

        if (!$insert && $this->isAttributeChanged('tel_no') && !$this->_smsVerificationConfirmed) {
            $this->sms_verified = self::SMS_NOT_VERIFY;
        }

        return true;
    }

    public function verifySmsPhone($phone)
    {
        if (!is_string($phone) || !preg_match('/^\+[1-9]\d{1,14}\z/', $phone)) {
            return false;
        }

        $this->_smsVerificationConfirmed = true;
        $this->tel_no = $phone;
        $this->sms_verified = self::SMS_VERIFY;

        try {
            if($this->validate(['tel_no'])) 
            {
                return $this->save(false, ['tel_no', 'sms_verified']);
            }
        } finally {
            $this->_smsVerificationConfirmed = false;
        }
    }

    public function beforeDelete()
    {
        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }
}
