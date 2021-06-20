<?php

namespace portalium\user\controllers\backend;

use portalium\user\models\Group;
use portalium\user\models\GroupSearch;
use portalium\user\models\UserSearch;
use portalium\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
use portalium\user\Module;

/**
 * GroupController implements the CRUD actions for Group model.
 */
class GroupController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => \yii\filters\AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Group models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Group model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
            'userNames' => array_column($model->getUsers()->asArray()->all(), 'username')
        ]);
    }

    /**
     * Creates a new Group model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Group();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Group model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $searchModel = new UserSearch();

        return $this->render('update', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams)
        ]);
    }

    /**
     * Manages members for an existing Group model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionMembers($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $model->load($this->request->post());
            if ($this->request->post('removeFromGroup')) {
                $model->scenario = Group::SCENARIO_DELETE_USERS;
                $model->setUserIds($this->request->post('removeUserIds'));
            } elseif ($this->request->post('addToGroup')) {
                $model->scenario = Group::SCENARIO_INSERT_USERS;
                $model->setUserIds($this->request->post('addUserIds'));
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Module::t('Settings saved.'));
                return $this->redirect(['members', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', Module::t('There was an error. Settings not saved successfully.'));
            }
        }

        $searchModel = new UserSearch();

        $searchModel->setGroupId($model->id);

        return $this->render('members', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProviderInGroup' => $searchModel->inGroup()->search($this->request->queryParams),
            'dataProviderOutGroup' => $searchModel->outGroup()->search($this->request->queryParams)
        ]);
    }

    /**
     * Deletes an existing Group model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Group model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Group the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Group::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(\Yii::t('site', 'The requested page does not exist.'));
    }
}
