<?php

namespace portalium\user\components;

use portalium\user\models\User;
use portalium\user\models\Group;

/**
 * Helper component for bulk assignment operations.
 */
class BulkAuthAssignmentHelper
{

    /**
     * Bulk assign by mixed both groups and users.
     * @param string $itemName
     * @param array $data
     * 
     * @return int Number of successful assign
     */
    public static function assignByMixed($itemName, $data)
    {
        $success = 0;

        if (!empty($data['groups'])) {
            foreach ($data['groups'] as $groupId) {
                $success += static::assignByGroupId($itemName, $groupId);
            }
        }

        if (!empty($data['users'])) {
            $success += static::assignByUserIds($itemName, $data['users']);
        }

        return $success;
    }

    /**
     * Bulk revoke by mixed both groups and users.
     * @param string $itemName
     * @param array $data
     * 
     * @return int Number of successful revoke
     */
    public static function revokeByMixed($itemName, $data)
    {
        $success = 0;

        if (!empty($data['groups'])) {
            foreach ($data['groups'] as $groupId) {
                $success += static::revokeByGroupId($itemName, $groupId);
            }
        }

        if (!empty($data['users'])) {
            $success += static::revokeByUserIds($itemName, $data['users']);
        }
    }

    /**
     * Bulk assign by group id.
     * @param string $itemName
     * @param int $groupId
     * 
     * @return int Number of successful assign
     */
    public static function assignByGroupId($itemName, $groupId)
    {
        $userIds = Group::findOne(['id' => $groupId])->getUsers()->select('id')->column();
        return static::assignByUserIds($itemName, $userIds);
    }

    /**
     * Bulk revoke by group id.
     * @param string $itemName
     * @param int $groupId
     * 
     * @return int Number of successful revoke
     */
    public static function revokeByGroupId($itemName, $groupId)
    {
        $ids = \Yii::$app->authManager->getUserIdsByRole($itemName);
        $groupUsers = Group::findOne(['id' => $groupId])->getUsers();
        $userIds = $groupUsers->where(['id' => $ids])->select('id')->column();
        return static::revokeByUserIds($itemName, $userIds);
    }

    /**
     * Bulk assign by user ids.
     * TODO: Batch insert refactor
     * @param string $itemName
     * @param array $userIds
     * 
     * @return int Number of successful assign
     */
    public static function assignByUserIds($itemName, $userIds)
    {
        $item = null;
        $success = 0;
        foreach ($userIds as $id) {
            try {
                if (empty($item)) {
                    $item = \Yii::$app->authManager->getRole($itemName);
                    $item = $item ?: \Yii::$app->authManager->getPermission($itemName);
                }
                \Yii::$app->authManager->assign($item, $id);
                $success++;
            } catch (\Exception $e) {
                \Yii::error($e->getMessage(), __METHOD__);
            }
        }
        return $success;
    }


    /**
     * Bulk revoke by user ids.
     * TODO: Batch delete refactor
     * @param string $itemName
     * @param array $userIds
     * 
     * @return int
     */
    public static function revokeByUserIds($itemName, $userIds)
    {
        $success = 0;
        foreach ($userIds as $id) {
            try {
                if (empty($item)) {
                    $item = \Yii::$app->authManager->getRole($itemName);
                    $item = $item ?: \Yii::$app->authManager->getPermission($itemName);
                }
                \Yii::$app->authManager->revoke($item, $id);
                $success++;
            } catch (\Exception $e) {
                \Yii::error($e->getMessage(), __METHOD__);
            }
        }
        return $success;
    }

    /**
     * Get assigned users as ActiveQuery.
     * @param string $itemName
     * @return \yii\db\ActiveQuery $users
     */
    public static function getAssignedUsers($itemName)
    {
        $ids = \Yii::$app->authManager->getUserIdsByRole($itemName);
        $provider = new \yii\data\ArrayDataProvider([
            'allModels' => $ids,
        ]);
        $users = User::find()
            ->where(['id_user' => $provider->getModels()]);
        return $users;
    }
}
