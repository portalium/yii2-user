<?php

namespace portalium\user\models\auth\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use portalium\user\models\User;

/**
 * AssignmentSearch represents the model behind the search form about Assignment.
 * 
 */
class AssignmentSearch extends Model
{

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $username;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'username'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('site', 'ID'),
            'username' => Yii::t('site', 'Username'),
            'name' => Yii::t('site', 'Name'),
        ];
    }

    /**
     * Create data provider for Assignment model.
     * @param array $params
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params)
    {
        $query = User::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'username', $this->username]);

        return $dataProvider;
    }
}
