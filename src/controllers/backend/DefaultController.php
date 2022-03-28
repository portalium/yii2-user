<?php

namespace portalium\user\controllers\backend;

use Yii;
use portalium\base\Event;
use portalium\user\Module;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use portalium\user\models\User;
use yii\web\NotFoundHttpException;
use portalium\user\models\UserForm;
use yii\web\ForbiddenHttpException;
use portalium\user\models\ModuleForm;
use portalium\user\models\UserSearch;
use portalium\user\Module as UserModule;
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
        if (!Yii::$app->user->can('viewUser'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to view User"));

        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

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
        if (!Yii::$app->user->can('createUser'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to create User"));

        $model = new UserForm();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if ($user = $model->createUser()) {
                    return $this->redirect(['view', 'id' => $user->id]);
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
        if (!Yii::$app->user->can('updateUser'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to Update User"));

        $model = $this->findModel($id);
        
        if ($this->request->isPost && $model->load($this->request->post())) {
            if($model->save()){
                return $this->redirect(['view', 'id' => $model->id]);
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
        if (!Yii::$app->user->can('deleteUser'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to delete User"));

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionDeleteManage($id){
        if (!Yii::$app->user->can('deleteUser'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to delete User"));
        $model = new ModuleForm();
        $modules = [];
        if($this->request->isPost && $model->load($this->request->post())){
            $modules = Yii::$app->getModules();
            foreach($modules as $key => $value){
                if(!empty($model->modules) && !in_array($key, $model->modules)){
                    unset($modules[$key]);
                }
            }
            if(empty($model->modules)){
                $modules = [];
            }
            Event::trigger($modules, UserModule::EVENT_USER_DELETE_BEFORE, new Event(['payload' => ['id' => $id, 'action' => 'delete', 'default_user' => $model->default_user]]));
            Event::trigger(Yii::$app->getModules(), UserModule::EVENT_USER_DELETE_BEFORE, new Event(['payload' => ['id' => $id, 'action' => 'transfer', 'default_user' => $model->default_user]]));
            $this->actionDelete($id);
        }
        

        foreach (Yii::$app->getModules() as $key => $module) {
            if(Event::hasHandlers($module::className(), UserModule::EVENT_USER_DELETE_BEFORE))
                {
                    $modules[$key] = $key;
                }
        }
        //get users array map for dropdown
        $users = ArrayHelper::map(User::find()->all(), 'id', 'username');

        return $this->render('delete-manage', [
            'model' => $model,
            'modules' => $modules,
            'id_user' => $id,
            'users' => $users,
        ]);
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
}
