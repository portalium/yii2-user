<?php

namespace portalium\user\models\auth\search;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\rbac\Item;

/**
 * AuthItemSearch represents the model behind the search form about AuthItem.
 */
class AuthItemSearch extends Model
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $type;

    /**
     * @var string
     */
    public $description;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'safe'],
            [['type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('site', 'Name'),
            'item_name' => Yii::t('site', 'Name'),
            'type' => Yii::t('site', 'Type'),
            'description' => Yii::t('site', 'Description'),
        ];
    }

    /**
     * Search AuthItem
     * @param array $params
     * @return \yii\data\ActiveDataProvider|\yii\data\ArrayDataProvider
     */
    public function search($params)
    {
        $manager = Yii::$app->authManager;

        if ($this->type == Item::TYPE_ROLE) {
            $items = $manager->getRoles();
        } else {
            $items = $manager->getPermissions();
        }

        $this->load($params);

        if ($this->validate()) {
            $search = mb_strtolower(trim($this->name));
            $desc = mb_strtolower(trim($this->description));
            foreach ($items as $name => $item) {
                $f = (empty($search) || mb_strpos(mb_strtolower($item->name), $search) !== false) &&
                    (empty($desc) || mb_strpos(mb_strtolower($item->description), $desc) !== false);
                if (!$f) {
                    unset($items[$name]);
                }
            }
        }

        return new ArrayDataProvider([
            'allModels' => $items,
        ]);
    }
}
