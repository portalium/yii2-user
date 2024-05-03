<?php

namespace portalium\user\controllers\web;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\rbac\Item;
use portalium\user\Module;
use portalium\user\models\User;
use portalium\user\models\UserForm;
use portalium\user\models\UserSearch;
use portalium\web\Controller as WebController;

/**
 * UserController implements the CRUD actions for User model.
 */
class DefaultController extends WebController
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
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (!\Yii::$app->user->can('userWebDefaultIndex') && !\Yii::$app->user->can('userWebDefaultIndexOwn')) {
            throw new \yii\web\ForbiddenHttpException(Module::t('You are not allowed to access this page.'));
        }
        
        if ($this->request->isPost) {
            $this->actionMultipleDelete($this->request->post('selection'));
        }

        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        if(!\Yii::$app->user->can('userWebDefaultIndex'))
            $dataProvider->query->andWhere(['id_user'=>\Yii::$app->user->id]);
        // $dataProvider->pagination->pageSize = 12;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        if (!Yii::$app->user->can('userWebDefaultView', ['model' => $this->findModel($id)]))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to view User"));

        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
            'groupNames' => $model->getGroups()->select('name')->column()
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can('userWebDefaultCreate'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to create User"));

        $model = new UserForm();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if ($user = $model->createUser()) {
                    Yii::$app->session->addFlash('success', Module::t('User has been created'));
                    return $this->redirect(['view', 'id' => $user->id_user]);
                }
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if (!Yii::$app->user->can('userWebDefaultUpdate', ['model' => $this->findModel($id)]))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to Update User"));

        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->username != $model->oldAttributes['username']) {
                $check = User::find()->where(['username' => $model->username])->one();
                if ($check) {
                    Yii::$app->session->addFlash('danger', Module::t('Username already exists'));
                    return $this->redirect(['view', 'id' => $model->id_user]);
                }
            }
            if ($model->email != $model->oldAttributes['email']) {
                $check = User::find()->where(['email' => $model->email])->one();
                if ($check) {
                    Yii::$app->session->addFlash('danger', Module::t('Email already exists'));
                    return $this->redirect(['view', 'id' => $model->id_user]);
                }
            }

            if($model->save()){
                Yii::$app->session->addFlash('success', Module::t('User has been updated'));
                return $this->redirect(['view', 'id' => $model->id_user]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if (!Yii::$app->user->can('userWebDefaultDelete', ['model' => $this->findModel($id)]))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to delete User"));

        if($this->findModel($id)->delete()){
            Yii::$app->session->addFlash('info', Module::t('User has been deleted'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('site', 'The requested page does not exist.'));
    }

    protected function actionMultipleDelete($selectedItems)
    {
        if (!Yii::$app->user->can('userWebDefaultDelete'))
        throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to delete User"));

        User::deleteAll(['id_user' => $selectedItems]);

        return $this->redirect(['index']);
    }
}
