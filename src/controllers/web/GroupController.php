<?php

namespace portalium\user\controllers\web;

use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
use portalium\user\Module;
use portalium\user\models\Group;
use portalium\user\models\GroupSearch;
use portalium\user\models\UserSearch;
use portalium\web\Controller as WebController;

/**
 * GroupController implements the CRUD actions for Group model.
 */
class GroupController extends WebController
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
        if (!\Yii::$app->user->can('userWebGroupIndex') && !\Yii::$app->user->can('userWebGroupIndexOwn')) {
            throw new \yii\web\ForbiddenHttpException(Module::t('You are not allowed to access this page.'));
        }

        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Group model.
     * @param integer $id_group
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        if (!Yii::$app->user->can('userWebGroupView'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to view Group"));
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
            'userNames' => $model->getUsers()->select('username')->column()
        ]);
    }

    /**
     * Creates a new Group model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can('userWebGroupCreate'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to create Group"));

        $model = new Group();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id_group]);
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
     * @param integer $id_group
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if (!Yii::$app->user->can('userWebGroupUpdate'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to update Group"));

        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->save();
            return $this->redirect(['view', 'id' => $model->id_group]);
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
     * @param integer $id_group
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionMembers($id)
    {
        if (!Yii::$app->user->can('userWebGroupMembers'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to members Group"));

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
                Yii::$app->session->addFlash('success', Module::t('Settings saved.'));
                return $this->redirect(['members', 'id' => $model->id_group]);
            } else {
                Yii::$app->session->addFlash('error', Module::t('There was an error. Settings not saved successfully.'));
            }
        }

        $searchModel = new UserSearch();

        $searchModel->setGroupId($model->id_group);

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
     * @param integer $id_group
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if (!Yii::$app->user->can('userWebGroupDelete'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to delete Group"));

        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Group model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id_group
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
