<?php

namespace portalium\user\models;

use portalium\user\Module;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "group".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $created_at
 * @property int $updated_at
 *
 * @property UserGroup[] $userGroups
 */
class Group extends \yii\db\ActiveRecord
{

    /**
     * Virtual attribute for UserIds
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
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
     * Gets group member Ids.
     * @return array
     */
    public function getCurrentUserIds()
    {
        return $this->getUserGroups()->select('user_id')->column();
    }


    public function setUserIds($userIds)
    {
        $this->_userIds = $userIds;
    }

    /**
     * Checks if not multidimensional and not empty array. 
     * 
     * @return void
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
        $userIds = (!empty($this->_userIds) && is_array($this->_userIds) && !is_array($this->_userIds[0])) ? $this->_userIds : [];
        $this->_userIds = $userIds;
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
            // Exec only update
            if ($this->mergeUserGroup() && $this->_isUserGroupModified) {
                $this->touch('updated_at');
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
    public function mergeUserGroup($userIds = [])
    {
        if (!empty($userIds)) {
            $this->setUserIds($userIds);
        }
        $this->validateUserIds();
        $oldUserIds = $this->getCurrentUserIds();
        $insertedIds = array_diff($this->_userIds, $oldUserIds);
        $deletedIds = array_diff($oldUserIds, $this->_userIds);
        $this->_isUserGroupModified = (count($insertedIds) > 0 || count($deletedIds) > 0) ? true : false;
        return $this->insertUsersToGroup($insertedIds) && $this->deleteUsersFromGroup($deletedIds);
    }

    /**
     * Insert users to group.
     * * Adds error to model if fails.
     * @param array $userIds
     * @return bool `true` if success every id, otherwise `false`.
     */
    public function insertUsersToGroup($userIds)
    {
        $isFailed = false;

        if (count($userIds) > 0) {
            $rows = [];
            foreach ($userIds as $userId) {
                $rows[] = [$userId, $this->id];
            }
            try {
                $numberAffectedRows = Yii::$app->db->createCommand()
                    ->batchInsert(UserGroup::getTableSchema()->fullName, ['user_id', 'group_id'], $rows)
                    ->execute();
            } catch (\yii\db\Exception $e) {
                $isFailed = true;
            }
            $isFailed = !($numberAffectedRows === count($userIds));
        }

        if ($isFailed) {
            $this->addError('*', Module::t('Inserting to group failed for {0} users.', [(count($userIds) - $numberAffectedRows)]));
            return false;
        }

        return true;
    }

     /**
     * Returns relational users data.
     * @return \use yii\db\ActiveQuery;
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable(UserGroup::getTableSchema()->fullName, ['group_id' => 'id']);
    }


    /**
     * Delete users from group.
     * * Adds error to model if fails.
     * @param array $userIds
     * @return bool `true` if success every id, otherwise `false`.
     */
    public function deleteUsersFromGroup($userIds)
    {
        $isFailed = false;

        if (count($userIds) > 0) {
            try {
                $numberAffectedRows = Yii::$app->db->createCommand('DELETE FROM '
                    . UserGroup::getTableSchema()->fullName
                    . ' WHERE group_id=:group_id AND user_id IN ('
                    . implode(', ', $userIds) . ')')
                    ->bindValue(':group_id', $this->id)
                    ->execute();
            } catch (\yii\db\Exception $e) {
                $isFailed = true;
            }
            $isFailed = !($numberAffectedRows === count($userIds));
        }

        if ($isFailed) {
            $this->addError('*', Module::t('Deleting from group failed for {0} users.', [(count($userIds) - $numberAffectedRows)]));
            return false;
        }

        return true;
    }
}
