<?php

namespace portalium\user\models;

use portalium\user\Module;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "group".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property `\yii\db\Expression('NOW()')` $created_at
 * @property `\yii\db\Expression('NOW()')` $updated_at
 * 
 * @property array $_userIds Virtual Attribute
 * @property bool $_isUserGroupModified
 *
 * @property UserGroup[] $userGroups
 */
class Group extends \yii\db\ActiveRecord
{

    const SCENARIO_INSERT_USERS = 'insert_users';
    const SCENARIO_DELETE_USERS = 'delete_users';

    /**
     * Virtual attribute for userIds
     * @var array
     */
    private $_userIds = [];

    /**
     * @var bool
     */
    private $_isUserGroupModified = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{group}}';
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_INSERT_USERS] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios[self::SCENARIO_DELETE_USERS] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Module::t('Group Name'),
            'description' => Module::t('Group Description'),
        ];
    }

    /**
     * Gets query for [[UserGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroups()
    {
        return $this->hasMany(UserGroup::class, ['group_id' => 'id']);
    }

    /**
     * Sets $userIds for merging
     * @return void
     */
    public function setUserIds($userIds)
    {
        $this->_userIds = !empty($userIds) ? $userIds : [];
    }

    /**
     * Checks if array and not multidimensional. 
     * 
     * @return bool
     * 
     * Valid:
     * ```php 
     * Array ([0] => 2,
     * [1] => 3,
     * [2] => 67,
     * [3] => 80,
     * [..] => ... )
     * ```
     */
    protected function validateUserIds()
    {
        return (is_array($this->_userIds) && count($this->_userIds) === count($this->_userIds, COUNT_RECURSIVE));
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if (!$insert) {
            if ($this->getScenario() !== self::SCENARIO_DEFAULT && $this->mergeUserGroup() && $this->_isUserGroupModified) {
                $this->touch('updated_at');
            }
        }
        if ($this->hasErrors()) {
            return false;
        }
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function beforeValidate($attributeNames = null, $clearErrors = true)
    {
        if ($this->getScenario() !== self::SCENARIO_DEFAULT) {
            if (!$this->validateUserIds()) {
                $this->addError('*', Module::t('userIds data not valid.'));
                return false;
            }
        }
        return true;
    }

    /**
     * Insert and delete $userIds 
     * @param array $userIds 
     * @return bool `true` if success every id, otherwise `false`.
     * * Does not touch if userId exists.
     */
    protected function mergeUserGroup()
    {
        $flag = false;

        if ($this->getScenario() === self::SCENARIO_INSERT_USERS) {
            $flag = $this->insertUsersToGroup($this->_userIds);
        } elseif ($this->getScenario() === self::SCENARIO_DELETE_USERS) {
            $flag = $this->deleteUsersFromGroup($this->_userIds);
        }

        $this->_isUserGroupModified = (count($this->_userIds) > 0 && $flag === true) ? true : false;
        return $flag;
    }

    /**
     * Insert users to group.
     * * Adds error to model if fails.
     * @param array $userIds
     * @return bool `true` if success every id, otherwise `false`.
     */
    protected function insertUsersToGroup($userIds)
    {
        $isFailed = false;

        if (count($userIds) > 0) {
            $rows = [];
            foreach ($userIds as $userId) {
                $rows[] = [$userId, $this->id];
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $numberAffectedRows = Yii::$app->db->createCommand()
                    ->batchInsert(UserGroup::getTableSchema()->fullName, ['user_id', 'group_id'], $rows)
                    ->execute();
                $transaction->commit();
            } catch (\Exception $e) {
                $isFailed = true;
                $transaction->rollBack();
            }
            $numberAffectedRows = !empty($numberAffectedRows) ? $numberAffectedRows : 0;
            $isFailed = !($numberAffectedRows === count($userIds));
        }

        if ($isFailed) {
            $this->addError('*', Module::t('Inserting to group failed for {0} users.', [(count($userIds) - $numberAffectedRows)]));
            return false;
        }

        return true;
    }

    /**
     * Delete users from group.
     * * Adds error to model if fails.
     * @param array $userIds
     * @return bool `true` if success every id, otherwise `false`.
     */
    protected function deleteUsersFromGroup($userIds)
    {
        $isFailed = false;

        if (count($userIds) > 0) {
            try {
                $transaction = Yii::$app->db->beginTransaction();
                $numberAffectedRows = Yii::$app->db->createCommand('DELETE FROM '
                    . UserGroup::getTableSchema()->fullName
                    . ' WHERE group_id=:group_id AND user_id IN ('
                    . implode(', ', $userIds) . ')')
                    ->bindValue(':group_id', $this->id)
                    ->execute();
                $transaction->commit();
            } catch (\Exception $e) {
                $isFailed = true;
                $transaction->rollBack();
            }
            $numberAffectedRows = !empty($numberAffectedRows) ? $numberAffectedRows : 0;
            $isFailed = !($numberAffectedRows === count($userIds));
        }

        if ($isFailed) {
            $this->addError('*', Module::t('Deleting from group failed for {0} users.', [(count($userIds) - $numberAffectedRows)]));
            return false;
        }

        return true;
    }

    /**
     * Returns relational users data.
     * @return \yii\db\ActiveQuery;
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable(UserGroup::getTableSchema()->fullName, ['group_id' => 'id']);
    }
}
