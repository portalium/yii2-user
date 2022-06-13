<?php

namespace portalium\user\models\auth;

use Yii;
use yii\helpers\Url;
use yii\base\Model;
use yii\rbac\Item;

use portalium\user\models\User;
use portalium\user\Module;


/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 *
 * @property Item $item
 *
 */
class AuthItem extends Model
{
    /**
     * @var string Name of permission or role
     */
    public $name;

    /**
     * @var int Auth item type
     */
    public $type;

    /**
     * @var string Description of permission or role
     */
    public $description;

    /**
     * @var Item
     */
    private $_item;

    /**
     * @var \yii\rbac\ManagerInterface
     */
    protected $manager;

    /**
     * Initialize object
     * @param Item $item
     * @param array $config
     */
    public function __construct($item = null, $config = [])
    {
        $this->manager = Yii::$app->authManager;

        $this->_item = $item;
        if ($item !== null) {
            $this->name = $item->name;
            $this->type = $item->type;
            $this->description = $item->description;
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['name'], 'checkUnique', 'when' => function () {
                return $this->isNewRecord || ($this->_item->name != $this->name);
            }],
            [['type'], 'integer'],
            [['description'], 'default'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * Check role is unique
     */
    public function checkUnique()
    {
        $value = $this->name;
        if ($this->manager->getRole($value) !== null || $this->manager->getPermission($value) !== null) {
            $message = Module::t('{attribute} "{value}" has already been taken.');
            $params = [
                'attribute' => $this->getAttributeLabel('name'),
                'value' => $value,
            ];
            $this->addError('name', Yii::$app->getI18n()->format($message, $params, Yii::$app->language));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Module::t('Name'),
            'type' => Module::t('Type'),
            'description' => Module::t('Description'),
        ];
    }

    /**
     * Check if is new record
     * @return boolean
     */
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }

    /**
     * Find role or permission
     * @param string $id
     * @return null|\self
     */
    public static function find($id)
    {
        $item = Yii::$app->authManager->getRole($id);
        $item = $item ?: Yii::$app->authManager->getPermission($id);

        if ($item !== null) {
            return new self($item);
        }

        return null;
    }

    /**
     * Save role to [[\yii\rbac\authManager]]
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            if ($this->_item === null) {
                if ($this->type == Item::TYPE_ROLE) {
                    $this->_item = $this->manager->createRole($this->name);
                } else {
                    $this->_item = $this->manager->createPermission($this->name);
                }
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            $this->_item->name = $this->name;
            $this->_item->description = $this->description;
            if ($isNew) {
                $this->manager->add($this->_item);
            } else {
                $this->manager->update($oldName, $this->_item);
            }
            return true;
        } else {
            return false;
        }
    }
    /**
     * Adds an item as a child of another item.
     * @param array $items
     * @return int
     */
    public function addChildren($items)
    {
        $success = 0;

        if ($this->_item) {
            foreach ($items as $name) {
                $child = $this->manager->getPermission($name);
                if ($this->type == Item::TYPE_ROLE && $child === null) {
                    $child = $this->manager->getRole($name);
                }
                try {
                    $this->manager->addChild($this->_item, $child);
                    $success++;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        return $success;
    }

    /**
     * Remove an item as a child of another item.
     * @param array $items
     * @return int 
     */
    public function removeChildren($items)
    {
        $success = 0;
        if ($this->_item !== null) {
            foreach ($items as $name) {
                $child = $this->manager->getPermission($name);
                if ($this->type == Item::TYPE_ROLE && $child === null) {
                    $child = $this->manager->getRole($name);
                }
                try {
                    $this->manager->removeChild($this->_item, $child);
                    $success++;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }

        return $success;
    }

    /**
     * Get items
     * @return array
     */
    public function getItems()
    {
        $available = [];
        $assigned = [];
        if ($this->type == Item::TYPE_ROLE) {
            foreach (array_keys($this->manager->getRoles()) as $name) {
                $available[$name] = 'role';
            }
        }
        foreach (array_keys($this->manager->getPermissions()) as $name) {
            $available[$name] = 'permission';
        }

        foreach ($this->manager->getChildren($this->_item->name) as $item) {
            $assigned[$item->name] = $item->type == 1 ? 'role' : 'permission';
            unset($available[$item->name]);
        }

        unset($available[$this->name]);
        ksort($available);
        ksort($assigned);
        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * Get users
     * @return array
     */
    public function getUsers()
    {
        $result = [];
        $ids = $this->manager->getUserIdsByRole($this->name);

        $provider = new \yii\data\ArrayDataProvider([
            'allModels' => $ids,
            'pagination' => [
                'pageSize' => 100,
            ]
        ]);

        $users = User::find()
            ->select(['id_user', 'username'])
            ->where(['id_user' => $provider->getModels()])
            ->asArray()->all();

        foreach ($users as &$row) {
            $row['link'] = Url::to(['/user/default/view', 'id' => $row['id_user']]);
        }

        $result['users'] = $users;
        $currentPage = $provider->pagination->getPage();
        $pageCount = $provider->pagination->getPageCount();
        if ($pageCount > 0) {
            $result['first'] = 0;
            $result['last'] = $pageCount - 1;
            if ($currentPage > 0) {
                $result['prev'] = $currentPage - 1;
            }
            if ($currentPage < $pageCount - 1) {
                $result['next'] = $currentPage + 1;
            }
        }
        return $result;
    }


    /**
     * Get item
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Get type name
     * @param  mixed $type
     * @return string|array
     */
    public static function getTypeName($type = null)
    {
        $result = [
            Item::TYPE_PERMISSION => 'Permission',
            Item::TYPE_ROLE => 'Role',
        ];
        if ($type === null) {
            return $result;
        }

        return $result[$type];
    }
}
