<?php

namespace portalium\user\models\auth;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Assignment
 */
class Assignment extends BaseObject
{
    /**
     * @var integer User id
     */
    public $userId;

    /**
     * @var \yii\web\IdentityInterface User
     */
    public $user;

    /**
     * @var \yii\rbac\ManagerInterface
     */
    protected $manager;

    /**
     * @inheritdoc
     */
    public function __construct($userId = null, $user = null, $config = [])
    {

        $this->userId = $userId ?: $user->getId();
        $this->user = $user;

        $this->manager = Yii::$app->authManager;

        if ($this->userId === null) {
            throw new InvalidConfigException('The "userId" property must be set.');
        }

        parent::__construct($config);
    }

    /**
     * Grands a roles from a user.
     * @param array $items
     * @return integer number of successful grand
     */
    public function assign($items)
    {
        $success = 0;
        foreach ($items as $name) {
            try {
                $item = $this->manager->getRole($name);
                $item = $item ?: $this->manager->getPermission($name);
                $this->manager->assign($item, $this->userId);
                $success++;
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
            }
        }
        return $success;
    }

    /**
     * Revokes a roles from a user.
     * @param array $items
     * @return integer number of successful revoke
     */
    public function revoke($items)
    {
        $success = 0;
        foreach ($items as $name) {
            try {
                $item = $this->manager->getRole($name);
                $item = $item ?: $this->manager->getPermission($name);
                $this->manager->revoke($item, $this->userId);
                $success++;
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
            }
        }

        return $success;
    }

    /**
     * Get all available and assigned roles/permission
     * @return array
     */
    public function getItems()
    {
        $available = [];
        $assigned = [];

        foreach (array_keys($this->manager->getRoles()) as $name) {
            $available[$name] = 'role';
        }

        foreach (array_keys($this->manager->getPermissions()) as $name) {
            if ($name[0] != '/') {
                $available[$name] = 'permission';
            }
        }

        foreach ($this->manager->getAssignments($this->userId) as $item) {
            $assigned[$item->roleName] = $available[$item->roleName];
            unset($available[$item->roleName]);
        }

        ksort($available);
        ksort($assigned);
        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($this->user) {
            return $this->user->$name;
        }
    }
}
