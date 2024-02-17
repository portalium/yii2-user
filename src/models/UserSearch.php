<?php

namespace portalium\user\models;

use Yii;
use yii\base\Model;
use portalium\data\ActiveDataProvider;
use portalium\user\models\User;
use portalium\user\Module;

/**
 * UserSearch represents the model behind the search form of `portalium\user\models\User`.
 */
class UserSearch extends User
{

    /**
     * @var yii\db\ActiveQuery
     */
    private $_query = null;

    /**
     * @var int|null
     */
    private $_groupId = null;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_user', 'status'], 'integer'],
            [['username', 'first_name', 'last_name', 'auth_key', 'password_hash', 'password_reset_token', 'email', 'access_token'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @param int $groupId
     * return void
     */
    public function setGroupId($groupId)
    {
        $this->_groupId = (int)$groupId;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {

        $query = !empty($this->_query) ? $this->_query : User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id_user' => $this->id_user,
            'status' => $this->status,
            'date_create' => $this->date_create,
            'date_update' => $this->date_update,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'access_token', $this->access_token]);

        return $dataProvider;
    }

    /**
     * Init query for group members.
     * @return $this|void the model object itself or void if not set groupId.
     */
    public function inGroup()
    {
        if (!empty($this->_groupId)) {
            $this->_query = User::find()->joinWith('groups')->where([Module::$tablePrefix.'group.id_group' => $this->_groupId]);
            return $this;
        }
    }

    /**
     * Init query for not group members.
     * TODO: Refactoring with using join.
     * @return $this|void the model object itself or void if not set groupId.
     */
    public function outGroup()
    {
        if (!empty($this->_groupId)) {
            $this->_query = User::find()->where([
                'not in', 'id_user',
                UserGroup::find()->select('id_user')->where(['id_group' => $this->_groupId])->asArray()->column()
            ]);
            return $this;
        }
    }
}
